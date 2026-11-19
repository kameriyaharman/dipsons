<?php
// Default content inserted the first time the database is set up.
// Everything here can be edited later from Admin → Services & fees / Website content.

return [
  'categories' => [
    ['rto', 'Vehicle documents & RTO', 1],
    ['devices', 'FASTag, GPS & safety devices', 2],
    ['repair', 'Repair & home services', 3],
  ],

  // slug, category, name, short_desc, tagline, tags, what_we_do, documents, icon, color, show_in_nav
  'services' => [
    ['insurance', 'rto', 'Insurance', 'New & renewal — car, bike, commercial; claim help',
      'New policy, renewal, transfer and claim help — for every vehicle.',
      'New policy, Renewal, Third party, Comprehensive, Claim help',
      "Compare quotes from multiple insurers for your vehicle\nThird-party and comprehensive (own damage) policies\nAdd-ons: zero depreciation, engine protect, roadside assistance\nPolicy in your WhatsApp the same day\nHelp with claim forms and follow-up",
      "RC (registration certificate)\nPrevious policy (for renewal)\nOwner ID — Aadhaar / PAN\nMobile number & email", 'shield', 'red', 1],

    ['pollution', 'rto', 'Pollution (PUC)', 'PUC certificate on the spot, all vehicles',
      'Pollution Under Control certificate in about 10 minutes — petrol, diesel & CNG.',
      'Two-wheeler, Car, Commercial, Same day',
      "Emission test done at our centre\nPUC certificate printed and sent on WhatsApp\nAll vehicles — bikes, cars, autos, trucks, buses, tractors\nReminder before your PUC expires",
      "Vehicle (bring it to the centre)\nRC copy or vehicle number", 'leaf', 'green', 1],

    ['road-tax', 'rto', 'Road Tax', 'Road tax, green tax & token tax payment',
      'Pay road tax, token tax and green tax without visiting the RTO.',
      'Road tax, Token tax, Green tax, Arrears',
      "Quarterly, yearly and lifetime road tax\nGreen tax for old vehicles\nPending tax & penalty calculation\nTax receipt on WhatsApp",
      "RC\nPrevious tax receipt (if any)\nInsurance copy", 'receipt', 'red', 0],

    ['fitness', 'rto', 'Fitness', 'Fitness certificate for commercial vehicles',
      'Fitness certificate (FC) for trucks, buses, autos and other commercial vehicles.',
      'New FC, Renewal, Commercial vehicles',
      "Fitness certificate renewal\nInspection booking & paperwork\nHelp with pending tax / PUC before fitness\nReminder before FC expires",
      "RC\nInsurance\nPUC certificate\nRoad tax receipt\nPermit (if applicable)", 'badge', 'green', 0],

    ['rc-renewal', 'rto', 'RC Renewal', 'Registration renewal after 15 years, duplicate RC',
      'Renew your registration certificate (RC) when it expires — usually after 15 years for private vehicles.',
      'RC renewal, Re-registration, Duplicate RC',
      "RC renewal for private vehicles after 15 years\nInspection & green tax help\nDuplicate RC if lost or damaged\nSmart card RC update",
      "Old RC\nInsurance\nPUC certificate\nOwner ID — Aadhaar / PAN", 'card', 'red', 0],

    ['ownership-transfer', 'rto', 'Name / Ownership Transfer', 'RC transfer after sale or on death of owner',
      'Transfer vehicle ownership (name transfer) after sale, gift or death of owner.',
      'After sale, On death of owner, Inter-state, HP termination',
      "Name transfer after buying/selling a vehicle\nTransfer on death of registered owner\nNOC for other state transfer\nHypothecation (loan) removal before transfer",
      "RC\nSale letter / Form 29 & 30 (we prepare)\nBuyer & seller ID — Aadhaar / PAN\nInsurance & PUC\nNOC from bank (if loan)", 'swap', 'green', 0],

    ['permit', 'rto', 'Permit', 'State, contract & goods carriage permit',
      'New and renewal of state permit, contract carriage and goods carriage permit.',
      'State permit, Contract carriage, Goods carriage, Renewal',
      "New permit application\nPermit renewal before expiry\nPermit transfer with vehicle sale\nTemporary / special permit",
      "RC\nInsurance\nFitness certificate\nRoad tax receipt\nOwner ID", 'doc', 'red', 0],

    ['national-permit', 'rto', 'National Permit', 'All-India permit for goods vehicles',
      'National permit (NP) and authorisation for goods carriers running across India.',
      'National permit, Authorisation, Renewal',
      "New national permit\nYearly authorisation fee payment\nRenewal before expiry\nHelp with fitness, tax & PUC needed for NP",
      "RC\nInsurance\nFitness certificate\nPUC\nRoad tax receipt\nOwner ID", 'globe', 'green', 0],

    ['trust-permit', 'rto', 'Trust Permit', 'Permit for trust / institution vehicles',
      'Permit work for vehicles registered to trusts, schools and institutions.',
      'New, Renewal, Institution vehicles',
      "New permit for trust / institution vehicles\nRenewal before expiry\nDocument preparation & RTO follow-up",
      "RC\nTrust / institution registration papers\nInsurance & fitness\nAuthorised person ID", 'building', 'red', 0],

    ['all-rto-work', 'rto', 'All RTO Work', 'Duplicate RC, HP add/remove, NOC, address change',
      'Every other RTO job handled for you — we file, follow up and deliver.',
      'Duplicate RC, HP add/remove, NOC, Address change',
      "Duplicate RC\nHypothecation add / remove\nNOC for other state\nAddress / mobile change in RC\nNew registration",
      "RC\nOwner ID — Aadhaar / PAN\nOther papers as per work (we tell you on WhatsApp)", 'rto', 'green', 1],

    ['driving-licence', 'rto', 'Driving Licence', 'Learner, new, renewal & duplicate DL',
      'Learner licence, permanent DL, renewal, duplicate and address change.',
      'Learner, New DL, Renewal, Duplicate',
      "Learner licence application & slot booking\nPermanent driving licence\nDL renewal & duplicate DL\nAddress change and class addition",
      "Aadhaar\nAge proof\nPassport photo\nMedical form (for some classes)", 'idcard', 'red', 0],

    ['fastag', 'devices', 'FASTag', 'New FASTag, recharge & KYC update',
      'Get a new FASTag for any vehicle, recharge it, or fix KYC / blacklist issues.',
      'New FASTag, Recharge, KYC, Blacklist fix',
      "New FASTag issued on the spot\nRecharge and balance help\nKYC update & blacklist removal\nFASTag replacement on vehicle sale",
      "RC\nOwner ID — Aadhaar / PAN\nVehicle photo", 'tag', 'green', 0],

    ['gps', 'devices', 'GPS Tracker', 'GPS tracking device fitting for any vehicle',
      'GPS tracker fitted on your vehicle with live tracking on your mobile.',
      'Live tracking, Fitting, Mobile app',
      "GPS device supply & fitting\nLive location on your phone\nIgnition / overspeed alerts\nRenewal of tracking plan",
      "RC\nOwner ID", 'pin', 'red', 0],

    ['sld', 'devices', 'SLD (Speed Limiter)', 'Speed limiting device for commercial vehicles',
      'Approved speed limiting device (SLD) fitting and certificate for transport vehicles.',
      'Fitting, Certificate, Fitness requirement',
      "Approved speed limiter supply & fitting\nSLD certificate for fitness / permit\nRepair & re-calibration",
      "RC\nOwner ID", 'gauge', 'green', 0],

    ['vltd', 'devices', 'VLTD', 'Vehicle location tracking device (AIS-140) for permit vehicles',
      'AIS-140 approved Vehicle Location Tracking Device with panic button, as required for public & goods permit vehicles.',
      'AIS-140, Panic button, Certificate',
      "AIS-140 VLTD supply & fitting\nPanic button installation\nDevice activation & certificate for fitness / permit\nYearly renewal",
      "RC\nPermit copy\nOwner ID", 'satellite', 'red', 0],

    ['vehicle-mechanic', 'repair', 'Vehicle Mechanic', 'Mechanic for bike, car & commercial — on call',
      'Trusted mechanic for breakdowns, starting problems and small repairs.',
      'Breakdown, On-site, Bike, Car, Truck',
      "On-call mechanic for breakdown\nStarting, battery & electrical issues\nPuncture and small repairs\nAll vehicle types",
      "Vehicle number\nLocation & problem details", 'wrench', 'green', 0],

    ['garage', 'repair', 'Garage / Vehicle Repairing', 'Full repair — engine, denting, painting',
      'Complete garage work — engine, suspension, denting-painting and accident repair.',
      'Engine, Denting, Painting, Accident repair',
      "Engine & gearbox repair\nDenting, painting & body work\nAccident repair with insurance claim help\nGenuine spare parts",
      "RC\nInsurance (for claim repairs)", 'garage', 'red', 0],

    ['car-service', 'repair', 'Car Service', 'Periodic service, oil change, washing',
      'Regular car servicing with oil change, filters, check-up and washing.',
      'Periodic service, Oil change, AC service, Washing',
      "Periodic service with oil & filter change\nBrake, battery & AC check\nCar wash & interior cleaning\nPick-up & drop on request",
      "Vehicle number\nService book (if any)", 'car', 'green', 0],

    ['plumber', 'repair', 'Plumber', 'Plumbing work for home & shop',
      'Experienced plumber for repairs, fittings and new connections.',
      'Leakage, Fitting, Tank, Bathroom',
      "Tap, pipe & leakage repair\nBathroom & kitchen fittings\nWater tank & motor connection\nNew pipeline work",
      "Address & work details", 'droplet', 'red', 0],
  ],

  'settings' => [
    'business_name' => 'DIPSONS',
    'business_tagline' => 'POLLUTION & INSURANCE CENTRE',
    'phone1' => '9954025322',
    'phone2' => '7002179752',
    'whatsapp' => '9954025322',
    'address_line1' => 'NRL Bharat Petrol Pump, 10 Mile',
    'address_line2' => 'Simlaguri, Baksa, Assam',
    'timings' => 'Mon–Sat, 9 AM – 7 PM',
    'map_query' => 'NRL Bharat Petrol Pump, Simlaguri, Baksa, Assam',
    'hero_title' => 'All vehicle RTO solutions under one roof',
    'hero_text' => 'Insurance, pollution certificate, road tax, fitness, ownership transfer, permit, FASTag, GPS and every RTO work — plus mechanic, garage and plumber services.',
    'callback_promise' => 'we call you back within 30 minutes',
    'about_text' => "Dipsons Pollution & Insurance Centre is a trusted local centre at NRL Bharat Petrol Pump, 10 Mile, Simlaguri. We help vehicle owners with insurance, PUC, road tax, fitness, permits, ownership transfer and every RTO job — quickly and with clear charges.\n\nWe also arrange FASTag, GPS, speed limiter (SLD) and VLTD fitting, plus trusted mechanics, garage repair, car service and plumbers.",
    'faq' => json_encode([
      ['Which documents are needed for insurance renewal?', 'RC, your previous policy and an ID (Aadhaar / PAN). Send them on WhatsApp and we share quotes from multiple insurers.'],
      ['How long does ownership transfer take?', 'Usually 7–15 working days depending on the RTO. We keep you updated on WhatsApp at every step.'],
      ['Can I get PUC for a commercial vehicle?', 'Yes — we do PUC for every vehicle: bikes, cars, autos, trucks, buses and tractors.'],
      ['Do you help with insurance claims?', 'Yes. We help fill claim forms, arrange garage repair and follow up with the insurer.'],
      ['Do you fit VLTD and speed limiters for permit vehicles?', 'Yes — approved AIS-140 VLTD and SLD devices with certificates needed for fitness and permit.'],
    ], JSON_UNESCAPED_UNICODE),
    'wa_templates' => json_encode([
      ['Documents needed', "Namaskar {name} ji 🙏 Thank you for your enquiry ({code}). For {services} please send: {documents} on this WhatsApp number. — Dipsons, NRL Bharat Petrol Pump, Simlaguri"],
      ['Enquiry received', "Namaskar {name} ji 🙏 We have received your enquiry {code} for {services}. We will call you shortly. — Dipsons, {phone}"],
      ['Work completed', "Namaskar {name} ji 🙏 Your work for {services} (enquiry {code}) is completed. Thank you for choosing Dipsons!"],
      ['Payment pending', "Namaskar {name} ji 🙏 For enquiry {code} ({services}) the charges are {charges}. Please pay at the centre or by UPI. — Dipsons"],
    ], JSON_UNESCAPED_UNICODE),
    'reminder_template' => "Namaskar {name} ji 🙏 Your {kind} for vehicle {vehicle} expires on {date}. Renew it at Dipsons — call/WhatsApp {phone}. NRL Bharat Petrol Pump, Simlaguri.",
  ],
];
