package com.dipsons.app;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.DownloadManager;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.os.Parcelable;
import android.provider.MediaStore;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.URLUtil;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.core.content.FileProvider;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import java.io.File;
import java.util.ArrayList;
import java.util.List;

/**
 * Dipsons app: a full-screen WebView showing the Dipsons customer app (/app).
 * No browser UI, no "Running in Chrome", no translate bar.
 */
public class MainActivity extends Activity {

    private static final int FILE_REQUEST = 101;

    private WebView web;
    private SwipeRefreshLayout swipe;
    private ProgressBar progress;
    private ImageView splash;
    private String host;

    private ValueCallback<Uri[]> filePathCallback;
    private Uri cameraUri;

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        host = getString(R.string.hostName);

        web = findViewById(R.id.web);
        swipe = findViewById(R.id.swipe);
        progress = findViewById(R.id.progress);
        splash = findViewById(R.id.splash);

        WebSettings s = web.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setDatabaseEnabled(true);
        s.setAllowFileAccess(false);
        s.setLoadWithOverviewMode(true);
        s.setUseWideViewPort(true);
        s.setSupportZoom(false);
        s.setMediaPlaybackRequiresUserGesture(false);
        s.setUserAgentString(s.getUserAgentString() + " DipsonsApp/" + BuildConfigHelper.versionName(this));

        CookieManager cm = CookieManager.getInstance();
        cm.setAcceptCookie(true);
        cm.setAcceptThirdPartyCookies(web, true);

        swipe.setColorSchemeResources(R.color.colorAccent, R.color.colorPrimary);
        swipe.setOnRefreshListener(() -> web.reload());
        // only allow pull-to-refresh when the page is scrolled to the top
        swipe.setOnChildScrollUpCallback((parent, child) -> web.getScrollY() > 0);

        web.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return handleUrl(request.getUrl());
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                progress.setVisibility(View.VISIBLE);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                progress.setVisibility(View.GONE);
                swipe.setRefreshing(false);
                hideSplash();
                CookieManager.getInstance().flush();
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
                if (request.isForMainFrame()) {
                    swipe.setRefreshing(false);
                    hideSplash();
                    String html = "<html><head><meta name='viewport' content='width=device-width,initial-scale=1'></head>"
                            + "<body style='font-family:sans-serif;text-align:center;padding:80px 24px;color:#14231a'>"
                            + "<h2 style='color:#1f6b35'>No internet connection</h2>"
                            + "<p>Please check your internet and try again.</p>"
                            + "<button onclick=\"location.href='" + startUrl() + "'\" style='margin-top:16px;background:#d4152f;color:#fff;border:0;border-radius:10px;padding:14px 28px;font-size:16px'>Try again</button>"
                            + "</body></html>";
                    view.loadDataWithBaseURL("https://" + host + "/", html, "text/html", "utf-8", null);
                }
            }
        });

        web.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int p) {
                progress.setProgress(p);
            }

            @Override
            public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> callback, FileChooserParams params) {
                if (filePathCallback != null) filePathCallback.onReceiveValue(null);
                filePathCallback = callback;
                return openFileChooser(params);
            }
        });

        web.setDownloadListener((url, userAgent, contentDisposition, mimeType, contentLength) ->
                download(url, userAgent, contentDisposition, mimeType));

        if (savedInstanceState != null) {
            web.restoreState(savedInstanceState);
        } else {
            Uri data = getIntent() != null ? getIntent().getData() : null;
            web.loadUrl(data != null && host.equals(data.getHost()) ? data.toString() : startUrl());
        }
    }

    private String startUrl() { return "https://" + host + "/app"; }

    private void hideSplash() {
        if (splash.getVisibility() == View.VISIBLE) {
            splash.animate().alpha(0f).setDuration(250).withEndAction(() -> splash.setVisibility(View.GONE));
        }
    }

    /** Our own pages stay in the app; calls, WhatsApp, maps and other sites open in their own apps. */
    private boolean handleUrl(Uri uri) {
        String scheme = uri.getScheme() == null ? "" : uri.getScheme();
        if (("https".equals(scheme) || "http".equals(scheme)) && host.equals(uri.getHost())) {
            String path = uri.getPath() == null ? "" : uri.getPath();
            if (path.startsWith("/app/file/")) { // customer documents: download with the login cookie
                download(uri.toString(), web.getSettings().getUserAgentString(), null, null);
                return true;
            }
            if (path.endsWith(".apk")) return true; // already in the app
            return false;
        }
        try {
            Intent i;
            if ("intent".equals(scheme)) {
                i = Intent.parseUri(uri.toString(), Intent.URI_INTENT_SCHEME);
            } else if ("tel".equals(scheme)) {
                i = new Intent(Intent.ACTION_DIAL, uri);
            } else {
                i = new Intent(Intent.ACTION_VIEW, uri);
            }
            startActivity(i);
        } catch (Exception e) {
            Toast.makeText(this, "No app found to open this link", Toast.LENGTH_SHORT).show();
        }
        return true;
    }

    private boolean openFileChooser(WebChromeClient.FileChooserParams params) {
        Intent pick = params.createIntent();
        pick.putExtra(Intent.EXTRA_ALLOW_MULTIPLE, params.getMode() == WebChromeClient.FileChooserParams.MODE_OPEN_MULTIPLE);

        List<Intent> extra = new ArrayList<>();
        cameraUri = null;
        try {
            File dir = new File(getCacheDir(), "camera");
            if (!dir.exists()) dir.mkdirs();
            File photo = File.createTempFile("doc_", ".jpg", dir);
            cameraUri = FileProvider.getUriForFile(this, "in.dipsons.app.fileprovider", photo);
            Intent cam = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
            cam.putExtra(MediaStore.EXTRA_OUTPUT, cameraUri);
            cam.addFlags(Intent.FLAG_GRANT_WRITE_URI_PERMISSION | Intent.FLAG_GRANT_READ_URI_PERMISSION);
            if (cam.resolveActivity(getPackageManager()) != null) extra.add(cam);
        } catch (Exception ignored) { cameraUri = null; }

        Intent chooser = Intent.createChooser(pick, "Upload document");
        chooser.putExtra(Intent.EXTRA_INITIAL_INTENTS, extra.toArray(new Parcelable[0]));
        try {
            startActivityForResult(chooser, FILE_REQUEST);
            return true;
        } catch (ActivityNotFoundException e) {
            filePathCallback = null;
            Toast.makeText(this, "Cannot open file picker", Toast.LENGTH_SHORT).show();
            return false;
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode != FILE_REQUEST || filePathCallback == null) return;
        Uri[] result = null;
        if (resultCode == RESULT_OK) {
            if (data != null && data.getClipData() != null) {
                int n = data.getClipData().getItemCount();
                result = new Uri[n];
                for (int i = 0; i < n; i++) result[i] = data.getClipData().getItemAt(i).getUri();
            } else if (data != null && data.getData() != null) {
                result = new Uri[]{data.getData()};
            } else if (cameraUri != null) {
                result = new Uri[]{cameraUri}; // photo taken with camera
            }
        }
        filePathCallback.onReceiveValue(result);
        filePathCallback = null;
    }

    private void download(String url, String userAgent, String contentDisposition, String mimeType) {
        try {
            DownloadManager.Request r = new DownloadManager.Request(Uri.parse(url));
            String cookies = CookieManager.getInstance().getCookie(url);
            if (cookies != null) r.addRequestHeader("Cookie", cookies);
            if (userAgent != null) r.addRequestHeader("User-Agent", userAgent);
            String name = URLUtil.guessFileName(url, contentDisposition, mimeType);
            r.setTitle(name);
            r.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
            r.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, "Dipsons/" + name);
            ((DownloadManager) getSystemService(DOWNLOAD_SERVICE)).enqueue(r);
            Toast.makeText(this, "Downloading… check notifications", Toast.LENGTH_SHORT).show();
        } catch (Exception e) {
            Toast.makeText(this, "Download failed", Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        Uri data = intent.getData();
        if (data != null && host.equals(data.getHost())) web.loadUrl(data.toString());
    }

    @Override
    public void onBackPressed() {
        if (web.canGoBack()) web.goBack();
        else super.onBackPressed();
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        web.saveState(outState);
    }

    @Override
    protected void onPause() {
        super.onPause();
        CookieManager.getInstance().flush();
    }
}
