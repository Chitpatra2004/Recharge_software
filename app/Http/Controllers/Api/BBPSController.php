<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BbpsTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BBPSController extends Controller
{
    // ── Biller catalogue (static; replace with live BBPS API call in production) ──
    private const BILLERS = [
        'electricity' => [
            ['id' => 'TPDDL',       'name' => 'TPDDL (Delhi)'],
            ['id' => 'BSES_RAJ',    'name' => 'BSES Rajdhani (Delhi)'],
            ['id' => 'BSES_YMR',    'name' => 'BSES Yamuna (Delhi)'],
            ['id' => 'NDPL',        'name' => 'North Delhi Power Ltd (NDPL)'],
            ['id' => 'BESCOM',      'name' => 'BESCOM (Karnataka)'],
            ['id' => 'HESCOM',      'name' => 'HESCOM (Karnataka)'],
            ['id' => 'GESCOM',      'name' => 'GESCOM (Karnataka)'],
            ['id' => 'MESCOM',      'name' => 'MESCOM (Karnataka)'],
            ['id' => 'CESC',        'name' => 'CESC (Kolkata)'],
            ['id' => 'MSEDCL',      'name' => 'MSEDCL (Maharashtra)'],
            ['id' => 'BEST',        'name' => 'BEST (Mumbai)'],
            ['id' => 'MAHAGENCO',   'name' => 'Mahagenco'],
            ['id' => 'TNEB',        'name' => 'TNEB / TANGEDCO (Tamil Nadu)'],
            ['id' => 'UPPCL_URBAN', 'name' => 'UPPCL Urban (Uttar Pradesh)'],
            ['id' => 'UPPCL_RURAL', 'name' => 'UPPCL Rural (Uttar Pradesh)'],
            ['id' => 'PVVNL',       'name' => 'PVVNL (UP Paschimanchal)'],
            ['id' => 'DVVNL',       'name' => 'DVVNL (UP Dakshinanchal)'],
            ['id' => 'MVVNL',       'name' => 'MVVNL (UP Madhyanchal)'],
            ['id' => 'KESCO',       'name' => 'KESCO (Kanpur)'],
            ['id' => 'WBSEDCL',     'name' => 'WBSEDCL (West Bengal)'],
            ['id' => 'CPCL',        'name' => 'CPCL (West Bengal)'],
            ['id' => 'APEPDCL',     'name' => 'APEPDCL (Andhra Pradesh East)'],
            ['id' => 'APSPDCL',     'name' => 'APSPDCL (Andhra Pradesh South)'],
            ['id' => 'TSSPDCL',     'name' => 'TSSPDCL (Telangana South)'],
            ['id' => 'TSNPDCL',     'name' => 'TSNPDCL (Telangana North)'],
            ['id' => 'PSPCL',       'name' => 'PSPCL (Punjab)'],
            ['id' => 'DHBVN',       'name' => 'DHBVN (Haryana Dakshin)'],
            ['id' => 'UHBVN',       'name' => 'UHBVN (Haryana Uttar)'],
            ['id' => 'JVVNL',       'name' => 'JVVNL (Rajasthan Jaipur)'],
            ['id' => 'AVVNL',       'name' => 'AVVNL (Rajasthan Ajmer)'],
            ['id' => 'JDVVNL',      'name' => 'JDVVNL (Rajasthan Jodhpur)'],
            ['id' => 'MGVCL',       'name' => 'MGVCL (Gujarat)'],
            ['id' => 'DGVCL',       'name' => 'DGVCL (Gujarat)'],
            ['id' => 'PGVCL',       'name' => 'PGVCL (Gujarat)'],
            ['id' => 'UGVCL',       'name' => 'UGVCL (Gujarat)'],
            ['id' => 'TORRENT_AHM', 'name' => 'Torrent Power (Ahmedabad)'],
            ['id' => 'TORRENT_SUR', 'name' => 'Torrent Power (Surat)'],
            ['id' => 'BPDB',        'name' => 'BPDB / BPDBL (Bihar)'],
            ['id' => 'SBPDCL',      'name' => 'SBPDCL (Bihar South)'],
            ['id' => 'NBPDCL',      'name' => 'NBPDCL (Bihar North)'],
            ['id' => 'JSEB',        'name' => 'JSEB (Jharkhand)'],
            ['id' => 'CSPDCL',      'name' => 'CSPDCL (Chhattisgarh)'],
            ['id' => 'MPPKVVCL',    'name' => 'MPPKVVCL (MP Paschim)'],
            ['id' => 'MPMKVVCL',    'name' => 'MPMKVVCL (MP Madhya)'],
            ['id' => 'MPEZ',        'name' => 'MPEZ (MP Poorv)'],
            ['id' => 'APDCL',       'name' => 'APDCL (Assam)'],
            ['id' => 'KPTCL',       'name' => 'KPTCL (Kerala)'],
            ['id' => 'KSEB',        'name' => 'KSEB (Kerala)'],
            ['id' => 'PSEB',        'name' => 'PSEB (Punjab)'],
            ['id' => 'HPSEB',       'name' => 'HPSEB (Himachal Pradesh)'],
            ['id' => 'JKPDD',       'name' => 'JKPDD (Jammu & Kashmir)'],
            ['id' => 'JPDCL',       'name' => 'JPDCL (Jammu)'],
            ['id' => 'KPDCL',       'name' => 'KPDCL (Kashmir)'],
            ['id' => 'UPCL',        'name' => 'UPCL (Uttarakhand)'],
            ['id' => 'TPODL',       'name' => 'TP Odisha (TPODL)'],
            ['id' => 'NESCO',       'name' => 'NESCO (Odisha)'],
            ['id' => 'SOUTHCO',     'name' => 'SOUTHCO (Odisha)'],
            ['id' => 'WESCO',       'name' => 'WESCO (Odisha)'],
            ['id' => 'JBVNL',       'name' => 'JBVNL (Jharkhand Bijli)'],
            ['id' => 'MANIPUR_SP',  'name' => 'MSPDCL (Manipur)'],
            ['id' => 'TSECL',       'name' => 'TSECL (Tripura)'],
            ['id' => 'ARUNACHAL_DG','name' => 'DoP (Arunachal Pradesh)'],
            ['id' => 'DNHPDCL',     'name' => 'DNHPDCL (Dadra & NH)'],
            ['id' => 'MSEDC_GOA',   'name' => 'MSEDC Goa'],
        ],
        'water' => [
            ['id' => 'DJB',      'name' => 'Delhi Jal Board (DJB)'],
            ['id' => 'BWSSB',    'name' => 'BWSSB (Bangalore)'],
            ['id' => 'MCGM',     'name' => 'MCGM (Mumbai)'],
            ['id' => 'HMWSSB',   'name' => 'HMWSSB (Hyderabad)'],
            ['id' => 'CMWSSB',   'name' => 'CMWSSB (Chennai)'],
            ['id' => 'GWMC',     'name' => 'GWMC (Greater Warangal)'],
            ['id' => 'NMMC',     'name' => 'NMMC (Navi Mumbai)'],
            ['id' => 'PCMC',     'name' => 'PCMC (Pune)'],
            ['id' => 'PMC',      'name' => 'PMC (Pune Municipal)'],
            ['id' => 'GHMC',     'name' => 'GHMC (Hyderabad)'],
            ['id' => 'BBMP',     'name' => 'BBMP (Bangalore)'],
            ['id' => 'KMC',      'name' => 'KMC (Kolkata)'],
            ['id' => 'AMC',      'name' => 'AMC (Ahmedabad)'],
            ['id' => 'SMC',      'name' => 'SMC (Surat)'],
            ['id' => 'JNPT',     'name' => 'JNPT Water'],
            ['id' => 'PHED_RJ',  'name' => 'PHED Rajasthan'],
            ['id' => 'PHED_MH',  'name' => 'PHED Maharashtra'],
            ['id' => 'PHED_UP',  'name' => 'PHED Uttar Pradesh'],
            ['id' => 'PHED_MP',  'name' => 'PHED Madhya Pradesh'],
            ['id' => 'PHED_HR',  'name' => 'PHED Haryana'],
            ['id' => 'PHED_PB',  'name' => 'PHED Punjab'],
            ['id' => 'PHED_GJ',  'name' => 'PHED Gujarat'],
            ['id' => 'PHED_BR',  'name' => 'PHED Bihar'],
            ['id' => 'PHED_OD',  'name' => 'PHED Odisha'],
        ],
        'gas' => [
            ['id' => 'IGL',      'name' => 'Indraprastha Gas (IGL) — Delhi'],
            ['id' => 'MGL',      'name' => 'Mahanagar Gas (MGL) — Mumbai'],
            ['id' => 'GAIL',     'name' => 'GAIL Gas'],
            ['id' => 'ADANI_GAS','name' => 'Adani Total Gas'],
            ['id' => 'GGCL',     'name' => 'Gujarat Gas (GGCL)'],
            ['id' => 'MNGL',     'name' => 'Maharashtra Natural Gas (MNGL)'],
            ['id' => 'BGL',      'name' => 'Bhagyanagar Gas (BGL) — Hyderabad'],
            ['id' => 'UNISON',   'name' => 'Unison Gas'],
            ['id' => 'HGL',      'name' => 'Haryana City Gas (HGL)'],
            ['id' => 'SABARMATI','name' => 'Sabarmati Gas'],
            ['id' => 'CENTRAL_U','name' => 'Central UP Gas'],
            ['id' => 'GREEN_GAS','name' => 'Green Gas (Lucknow/Agra)'],
            ['id' => 'TRIPURA_G','name' => 'Tripura Natural Gas'],
            ['id' => 'BPCL_GAS', 'name' => 'BPCL Piped Gas'],
            ['id' => 'CHAROTAR', 'name' => 'Charotar Gas (Gujarat)'],
            ['id' => 'VADODARA', 'name' => 'Vadodara Gas'],
            ['id' => 'SHRIJI',   'name' => 'Shriji Gas'],
            ['id' => 'AG_GAS',   'name' => 'AG&P Pratham Gas'],
        ],
        'dth' => [
            ['id' => 'TATAPLAY',  'name' => 'Tata Play (Tata Sky)'],
            ['id' => 'DISHTV',    'name' => 'Dish TV'],
            ['id' => 'D2H',       'name' => 'D2H (Videocon D2H)'],
            ['id' => 'AIRTELDT',  'name' => 'Airtel Digital TV'],
            ['id' => 'SUNDIRECT', 'name' => 'Sun Direct'],
        ],
        'broadband' => [
            ['id' => 'AIRTEL_BB',    'name' => 'Airtel Broadband / Xstream Fiber'],
            ['id' => 'JIOFIBER',     'name' => 'JioFiber'],
            ['id' => 'BSNL_BB',      'name' => 'BSNL Broadband'],
            ['id' => 'ACT_FIBERNET', 'name' => 'ACT Fibernet'],
            ['id' => 'EXCITEL',      'name' => 'Excitel'],
            ['id' => 'HATHWAY',      'name' => 'Hathway Broadband'],
            ['id' => 'YOU_BROADBAND','name' => 'You Broadband'],
            ['id' => 'TIKONA',       'name' => 'Tikona Infinet'],
            ['id' => 'SITI_CABLE',   'name' => 'Siti Broadband'],
            ['id' => 'DEN_BROADBAND','name' => 'DEN Broadband'],
            ['id' => 'SPECTRANET',   'name' => 'Spectranet'],
            ['id' => 'ALLIANCE',     'name' => 'Alliance Broadband'],
            ['id' => 'BEAM_FIBER',   'name' => 'Beam Fiber (Hyderabad)'],
            ['id' => 'GTPL',         'name' => 'GTPL Broadband'],
            ['id' => 'CONNECT_BB',   'name' => 'Connect Broadband (Punjab)'],
            ['id' => 'ASIANET',      'name' => 'Asianet Broadband (Kerala)'],
            ['id' => 'MTNL_BB',      'name' => 'MTNL Broadband'],
            ['id' => 'RAILWIRE',     'name' => 'RailWire (RailTel)'],
        ],
        'landline' => [
            ['id' => 'BSNL_LL',   'name' => 'BSNL Landline'],
            ['id' => 'MTNL_DEL',  'name' => 'MTNL Landline (Delhi)'],
            ['id' => 'MTNL_MUM',  'name' => 'MTNL Landline (Mumbai)'],
            ['id' => 'AIRTEL_LL', 'name' => 'Airtel Landline'],
            ['id' => 'JIO_LL',    'name' => 'JioFiber Landline / VoIP'],
            ['id' => 'TATA_LL',   'name' => 'Tata Teleservices Landline'],
            ['id' => 'HFCL',      'name' => 'HFCL Infotel Landline'],
        ],
        'insurance' => [
            ['id' => 'LIC',           'name' => 'LIC of India'],
            ['id' => 'HDFC_LIFE',     'name' => 'HDFC Life Insurance'],
            ['id' => 'ICICI_PRU',     'name' => 'ICICI Prudential Life'],
            ['id' => 'SBI_LIFE',      'name' => 'SBI Life Insurance'],
            ['id' => 'BAJAJ_LIFE',    'name' => 'Bajaj Allianz Life Insurance'],
            ['id' => 'MAX_LIFE',      'name' => 'Max Life Insurance'],
            ['id' => 'KOTAK_LIFE',    'name' => 'Kotak Mahindra Life Insurance'],
            ['id' => 'TATA_AIA',      'name' => 'Tata AIA Life Insurance'],
            ['id' => 'ADITYA_LIFE',   'name' => 'Aditya Birla Sun Life Insurance'],
            ['id' => 'RELIANCE_LIFE', 'name' => 'Reliance Nippon Life Insurance'],
            ['id' => 'PNB_METLIFE',   'name' => 'PNB MetLife Insurance'],
            ['id' => 'CANARA_HSBC',   'name' => 'Canara HSBC OBC Life Insurance'],
            ['id' => 'FUTURE_GENE',   'name' => 'Future Generali Life Insurance'],
            ['id' => 'BHARTI_AXA',    'name' => 'Bharti AXA Life Insurance'],
            ['id' => 'EXIDE_LIFE',    'name' => 'Exide Life Insurance'],
            ['id' => 'ORIENTAL_INS',  'name' => 'Oriental Insurance (General)'],
            ['id' => 'NEW_INDIA',     'name' => 'New India Assurance'],
            ['id' => 'NIAC',          'name' => 'National Insurance Company'],
            ['id' => 'UNITED_INDIA',  'name' => 'United India Insurance'],
            ['id' => 'ICICI_LOMBARD', 'name' => 'ICICI Lombard General Insurance'],
            ['id' => 'HDFC_ERGO',     'name' => 'HDFC ERGO General Insurance'],
            ['id' => 'STAR_HEALTH',   'name' => 'Star Health & Allied Insurance'],
            ['id' => 'NIVA_BUPA',     'name' => 'Niva Bupa Health Insurance'],
            ['id' => 'CARE_HEALTH',   'name' => 'Care Health Insurance'],
        ],
        'loan' => [
            ['id' => 'SBI_LOAN',      'name' => 'SBI Loan EMI'],
            ['id' => 'HDFC_LOAN',     'name' => 'HDFC Bank Loan EMI'],
            ['id' => 'ICICI_LOAN',    'name' => 'ICICI Bank Loan EMI'],
            ['id' => 'AXIS_LOAN',     'name' => 'Axis Bank Loan EMI'],
            ['id' => 'PNB_LOAN',      'name' => 'PNB Loan EMI'],
            ['id' => 'BOB_LOAN',      'name' => 'Bank of Baroda Loan EMI'],
            ['id' => 'CANARA_LOAN',   'name' => 'Canara Bank Loan EMI'],
            ['id' => 'UNION_LOAN',    'name' => 'Union Bank Loan EMI'],
            ['id' => 'INDIAN_LOAN',   'name' => 'Indian Bank Loan EMI'],
            ['id' => 'KOTAK_LOAN',    'name' => 'Kotak Mahindra Bank Loan EMI'],
            ['id' => 'YESBANK_LOAN',  'name' => 'Yes Bank Loan EMI'],
            ['id' => 'IDBI_LOAN',     'name' => 'IDBI Bank Loan EMI'],
            ['id' => 'INDUSIND_LOAN', 'name' => 'IndusInd Bank Loan EMI'],
            ['id' => 'BAJAJ_FIN',     'name' => 'Bajaj Finance EMI'],
            ['id' => 'BAJAJ_HOUSING', 'name' => 'Bajaj Housing Finance EMI'],
            ['id' => 'TATA_CAP',      'name' => 'Tata Capital Loan EMI'],
            ['id' => 'L_T_FINANCE',   'name' => 'L&T Finance Loan EMI'],
            ['id' => 'MAHINDRA_FIN',  'name' => 'Mahindra Finance Loan EMI'],
            ['id' => 'SHRIRAM_FIN',   'name' => 'Shriram Finance Loan EMI'],
            ['id' => 'CHOLAMANDALAM', 'name' => 'Cholamandalam Investment EMI'],
            ['id' => 'MUTHOOT_FIN',   'name' => 'Muthoot Finance'],
            ['id' => 'FULLERTON',     'name' => 'Fullerton India Credit EMI'],
            ['id' => 'ADITYA_CAP',    'name' => 'Aditya Birla Capital Loan EMI'],
            ['id' => 'HDB_FIN',       'name' => 'HDB Financial Services EMI'],
            ['id' => 'HERO_FINCORP',  'name' => 'Hero FinCorp Loan EMI'],
            ['id' => 'INDIABULLS',    'name' => 'Indiabulls Housing Finance EMI'],
            ['id' => 'HOME_FIRST',    'name' => 'Home First Finance EMI'],
            ['id' => 'PIRAMAL_FIN',   'name' => 'Piramal Finance EMI'],
        ],
        'fastag' => [
            ['id' => 'SBI_FT',     'name' => 'SBI FASTag'],
            ['id' => 'HDFC_FT',    'name' => 'HDFC Bank FASTag'],
            ['id' => 'ICICI_FT',   'name' => 'ICICI Bank FASTag'],
            ['id' => 'AXIS_FT',    'name' => 'Axis Bank FASTag'],
            ['id' => 'PAYTM_FT',   'name' => 'Paytm Payments Bank FASTag'],
            ['id' => 'KOTAK_FT',   'name' => 'Kotak Mahindra Bank FASTag'],
            ['id' => 'PNB_FT',     'name' => 'PNB FASTag'],
            ['id' => 'BOB_FT',     'name' => 'Bank of Baroda FASTag'],
            ['id' => 'IDBI_FT',    'name' => 'IDBI Bank FASTag'],
            ['id' => 'FEDERAL_FT', 'name' => 'Federal Bank FASTag'],
            ['id' => 'INDUSIND_FT','name' => 'IndusInd Bank FASTag'],
            ['id' => 'IDFC_FT',    'name' => 'IDFC First Bank FASTag'],
            ['id' => 'AIRTEL_FT',  'name' => 'Airtel Payments Bank FASTag'],
            ['id' => 'FINO_FT',    'name' => 'Fino Payments Bank FASTag'],
            ['id' => 'NHAI_FT',    'name' => 'NHAI FASTag (My FASTag)'],
        ],
        'credit_card' => [
            ['id' => 'SBI_CC',      'name' => 'SBI Credit Card'],
            ['id' => 'HDFC_CC',     'name' => 'HDFC Bank Credit Card'],
            ['id' => 'ICICI_CC',    'name' => 'ICICI Bank Credit Card'],
            ['id' => 'AXIS_CC',     'name' => 'Axis Bank Credit Card'],
            ['id' => 'AMEX_CC',     'name' => 'American Express Credit Card'],
            ['id' => 'CITI_CC',     'name' => 'Citi Bank Credit Card'],
            ['id' => 'KOTAK_CC',    'name' => 'Kotak Mahindra Credit Card'],
            ['id' => 'YESBANK_CC',  'name' => 'Yes Bank Credit Card'],
            ['id' => 'INDUSIND_CC', 'name' => 'IndusInd Bank Credit Card'],
            ['id' => 'BOB_CC',      'name' => 'Bank of Baroda Credit Card'],
            ['id' => 'RBL_CC',      'name' => 'RBL Bank Credit Card'],
            ['id' => 'IDFC_CC',     'name' => 'IDFC First Bank Credit Card'],
            ['id' => 'AU_CC',       'name' => 'AU Small Finance Bank Credit Card'],
            ['id' => 'SC_CC',       'name' => 'Standard Chartered Credit Card'],
            ['id' => 'HSBC_CC',     'name' => 'HSBC Credit Card'],
        ],
        'municipal_tax' => [
            ['id' => 'MCGM_TAX',   'name' => 'MCGM Property Tax (Mumbai)'],
            ['id' => 'BBMP_TAX',   'name' => 'BBMP Property Tax (Bangalore)'],
            ['id' => 'GHMC_TAX',   'name' => 'GHMC Property Tax (Hyderabad)'],
            ['id' => 'NDMC_TAX',   'name' => 'NDMC Property Tax (Delhi)'],
            ['id' => 'SDMC_TAX',   'name' => 'SDMC Property Tax (South Delhi)'],
            ['id' => 'EDMC_TAX',   'name' => 'EDMC Property Tax (East Delhi)'],
            ['id' => 'AMC_TAX',    'name' => 'AMC Property Tax (Ahmedabad)'],
            ['id' => 'SMC_TAX',    'name' => 'SMC Property Tax (Surat)'],
            ['id' => 'PMC_TAX',    'name' => 'PMC Property Tax (Pune)'],
            ['id' => 'NMC_TAX',    'name' => 'NMC Property Tax (Nagpur)'],
            ['id' => 'KMC_TAX',    'name' => 'KMC Property Tax (Kolkata)'],
            ['id' => 'CMC_TAX',    'name' => 'CMC Property Tax (Chennai)'],
            ['id' => 'LMCP_TAX',   'name' => 'LMC Property Tax (Lucknow)'],
            ['id' => 'JMC_TAX',    'name' => 'JMC Property Tax (Jaipur)'],
            ['id' => 'IMC_TAX',    'name' => 'IMC Property Tax (Indore)'],
            ['id' => 'PCMC_TAX',   'name' => 'PCMC Property Tax (Pimpri-Chinchwad)'],
            ['id' => 'BDA_TAX',    'name' => 'BDA Property Tax (Bhubaneswar)'],
            ['id' => 'VDA_TAX',    'name' => 'VDA Property Tax (Varanasi)'],
            ['id' => 'CDA_TAX',    'name' => 'CDA Property Tax (Cuttack)'],
        ],
        'education' => [
            ['id' => 'DU_FEE',       'name' => 'Delhi University'],
            ['id' => 'MU_FEE',       'name' => 'Mumbai University'],
            ['id' => 'BANGALORE_UNI','name' => 'Bangalore University'],
            ['id' => 'JNTU_HYD',    'name' => 'JNTU Hyderabad'],
            ['id' => 'OSMANIA_UNI',  'name' => 'Osmania University'],
            ['id' => 'ANNA_UNI',     'name' => 'Anna University'],
            ['id' => 'PUNE_UNI',     'name' => 'Savitribai Phule Pune University'],
            ['id' => 'RAJASTHAN_UNI','name' => 'University of Rajasthan'],
            ['id' => 'LUCKNOW_UNI',  'name' => 'University of Lucknow'],
            ['id' => 'BHU',          'name' => 'Banaras Hindu University'],
            ['id' => 'AMU',          'name' => 'Aligarh Muslim University'],
            ['id' => 'JMI',          'name' => 'Jamia Millia Islamia'],
            ['id' => 'IIT_FEES',     'name' => 'IIT Fee Payment'],
            ['id' => 'NIT_FEES',     'name' => 'NIT Fee Payment'],
            ['id' => 'CBSE_FEES',    'name' => 'CBSE Exam Fee'],
            ['id' => 'ICSE_FEES',    'name' => 'ICSE Exam Fee'],
            ['id' => 'NIOS_FEES',    'name' => 'NIOS Fee Payment'],
        ],
        'subscription' => [
            ['id' => 'NETFLIX',      'name' => 'Netflix Subscription'],
            ['id' => 'AMAZON_PRIME', 'name' => 'Amazon Prime'],
            ['id' => 'HOTSTAR',      'name' => 'Disney+ Hotstar'],
            ['id' => 'SONY_LIV',     'name' => 'Sony LIV'],
            ['id' => 'ZEE5',         'name' => 'ZEE5'],
            ['id' => 'JIOCINEMA',    'name' => 'JioCinema Premium'],
            ['id' => 'YOUTUBE_PRM',  'name' => 'YouTube Premium'],
            ['id' => 'SPOTIFY',      'name' => 'Spotify Premium'],
            ['id' => 'JIO_SAAVN',    'name' => 'JioSaavn Pro'],
            ['id' => 'GAANA',        'name' => 'Gaana+'],
            ['id' => 'MXPLAYER',     'name' => 'MX Player Premium'],
            ['id' => 'VOOT',         'name' => 'Voot Select'],
            ['id' => 'ALT_BALAJI',   'name' => 'ALTBalaji'],
        ],
    ];

    /** GET /api/v1/bbps/billers?category=electricity */
    public function billers(Request $request): JsonResponse
    {
        $cat     = strtolower($request->input('category', ''));
        $billers = self::BILLERS[$cat] ?? [];

        return response()->json(['category' => $cat, 'billers' => $billers]);
    }

    /** POST /api/v1/bbps/fetch-bill — fetch bill details before payment */
    public function fetchBill(Request $request): JsonResponse
    {
        $data = $request->validate([
            'biller_id'       => ['required', 'string'],
            'consumer_number' => ['required', 'string', 'max:50'],
        ]);

        // ── Try real BBPS fetch API if configured ─────────────────────────
        $apiEndpoint = config('recharge.bbps_fetch_endpoint');

        if ($apiEndpoint) {
            try {
                $response = Http::timeout(8)
                    ->connectTimeout(5)
                    ->withHeaders([
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . config('recharge.bbps_api_key'),
                    ])
                    ->post($apiEndpoint, [
                        'biller_id'       => $data['biller_id'],
                        'consumer_number' => $data['consumer_number'],
                    ]);

                if ($response->successful()) {
                    return response()->json($response->json());
                }

                Log::warning('BBPS fetch-bill API returned non-success', [
                    'biller_id' => $data['biller_id'],
                    'status'    => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::error('BBPS fetch-bill API error', ['error' => $e->getMessage()]);
            }
        }

        // ── Fallback: simulated bill data (remove when live API is ready) ─
        $dueAmount = round(mt_rand(200, 5000) + (mt_rand(0, 99) / 100), 2);
        $dueDate   = now()->addDays(mt_rand(3, 20))->toDateString();

        return response()->json([
            'consumer_number' => $data['consumer_number'],
            'consumer_name'   => 'Customer ' . strtoupper(substr($data['consumer_number'], -4)),
            'bill_number'     => 'BILL' . strtoupper(Str::random(8)),
            'due_date'        => $dueDate,
            'due_amount'      => $dueAmount,
            'bill_period'     => now()->subMonth()->format('M Y'),
            'units_consumed'  => mt_rand(100, 800),
        ]);
    }

    /**
     * POST /api/v1/bbps/pay
     *
     * Synchronous flow:
     *   1. Validate input
     *   2. DB::transaction: lock wallet → debit → create txn (status=processing)
     *   3. Call BBPS operator API with Guzzle (timeout: 10 s)
     *   4a. API success → mark txn 'success'           (committed)
     *   4b. API failure → refund wallet + mark 'failed' (committed)
     *   4c. API timeout → leave txn 'pending'           (cron will retry)
     *   5. Return result immediately
     */
    public function pay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'biller_category' => ['required', 'in:electricity,water,gas,dth,broadband,landline,insurance,loan,fastag,credit_card,municipal_tax,education,subscription'],
            'biller_id'       => ['required', 'string', 'max:30'],
            'biller_name'     => ['required', 'string', 'max:100'],
            'consumer_number' => ['required', 'string', 'max:100'],
            'amount'          => ['required', 'numeric', 'min:1', 'max:100000'],
            'bill_details'    => ['sometimes', 'nullable', 'array'],
            'idempotency_key' => ['sometimes', 'nullable', 'string', 'max:128'],
        ]);

        $user   = $request->user();
        $amount = (float) $data['amount'];
        $txnId  = 'BBPS' . strtoupper(Str::random(12));

        // ── Duplicate prevention via idempotency key ───────────────────────
        if (! empty($data['idempotency_key'])) {
            $existing = BbpsTransaction::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return response()->json([
                    'message'       => 'Duplicate transaction.',
                    'txn_id'        => $existing->txn_id,
                    'status'        => $existing->status,
                    'amount'        => $existing->amount,
                    'balance_after' => $existing->balance_after,
                ], 409);
            }
        }

        // ── Step 1 & 2: Debit wallet atomically, persist transaction ───────
        // Debit happens BEFORE the API call so funds are secured.
        // On API failure the debit is reversed inside the same or a new txn.
        try {
            $bbpsTxn = DB::transaction(function () use ($user, $data, $amount, $txnId) {
                $wallet = DB::table('wallets')
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    throw new \RuntimeException('Wallet not found.');
                }

                if ((float) $wallet->balance < $amount) {
                    throw new \RuntimeException(
                        'Insufficient wallet balance. Available: ₹' . number_format((float) $wallet->balance, 2)
                    );
                }

                $balBefore = (float) $wallet->balance;
                $balAfter  = round($balBefore - $amount, 2);

                DB::table('wallets')->where('user_id', $user->id)->update([
                    'balance'         => $balAfter,
                    'total_recharged' => DB::raw("total_recharged + {$amount}"),
                    'updated_at'      => now(),
                ]);

                DB::table('wallet_transactions')->insert([
                    'user_id'       => $user->id,
                    'type'          => 'debit',
                    'amount'        => $amount,
                    'balance_after' => $balAfter,
                    'description'   => ucfirst($data['biller_category']) . ' bill — ' . $data['biller_name'],
                    'reference'     => $txnId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                return BbpsTransaction::create([
                    'user_id'         => $user->id,
                    'idempotency_key' => $data['idempotency_key'] ?? null,
                    'biller_category' => $data['biller_category'],
                    'biller_id'       => $data['biller_id'],
                    'biller_name'     => $data['biller_name'],
                    'consumer_number' => $data['consumer_number'],
                    'amount'          => $amount,
                    'balance_before'  => $balBefore,
                    'balance_after'   => $balAfter,
                    'status'          => 'processing',
                    'txn_id'          => $txnId,
                    'biller_ref_id'   => null,
                    'bill_details'    => $data['bill_details'] ?? null,
                ]);
            });

        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('BBPS pay: wallet debit failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Payment could not be initiated. Please try again.'], 500);
        }

        // ── Step 3: Call operator BBPS API synchronously ───────────────────
        $apiEndpoint = config('recharge.bbps_pay_endpoint');
        $apiSuccess  = false;
        $billerRef   = null;
        $timedOut    = false;
        $apiStatus   = null;

        if ($apiEndpoint) {
            try {
                $startTime = microtime(true);

                $response = Http::timeout((int) config('recharge.sync_timeout', 10))
                    ->connectTimeout((int) config('recharge.connect_timeout', 5))
                    ->withHeaders([
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . config('recharge.bbps_api_key'),
                    ])
                    ->post($apiEndpoint, [
                        'txn_id'          => $txnId,
                        'biller_id'       => $data['biller_id'],
                        'biller_category' => $data['biller_category'],
                        'consumer_number' => $data['consumer_number'],
                        'amount'          => $amount,
                        'bill_details'    => $data['bill_details'] ?? null,
                    ]);

                $duration  = (int) ((microtime(true) - $startTime) * 1000);
                $apiStatus = $this->normaliseBbpsStatus($response->json());

                Log::info('BBPS API response', [
                    'txn_id'      => $txnId,
                    'http_status' => $response->status(),
                    'api_status'  => $apiStatus,
                    'duration_ms' => $duration,
                ]);

                if ($response->successful() && $apiStatus === 'success') {
                    $apiSuccess = true;
                    $billerRef  = $response->json('biller_ref_id')
                               ?? $response->json('ref_id')
                               ?? $response->json('txn_id');
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $timedOut = true;
                Log::warning('BBPS API timeout', ['txn_id' => $txnId, 'error' => $e->getMessage()]);

            } catch (\Throwable $e) {
                Log::error('BBPS API error', ['txn_id' => $txnId, 'error' => $e->getMessage()]);
            }
        } else {
            // No BBPS API configured — treat as success (sandbox/demo mode)
            $apiSuccess = true;
            $billerRef  = 'REF' . strtoupper(Str::random(10));
        }

        // ── Step 4: Finalise based on API result ───────────────────────────

        if ($apiSuccess) {
            // 4a: Success — update transaction status
            $bbpsTxn->update([
                'status'        => 'success',
                'biller_ref_id' => $billerRef,
                'processed_at'  => now(),
            ]);

            return response()->json([
                'message'       => 'Bill payment successful.',
                'txn_id'        => $bbpsTxn->txn_id,
                'biller_ref_id' => $billerRef,
                'amount'        => $bbpsTxn->amount,
                'status'        => 'success',
                'balance_after' => $bbpsTxn->balance_after,
            ], 201);
        }

        if ($timedOut) {
            // 4c: Timeout — leave as 'pending', cron will retry
            $bbpsTxn->update([
                'status'       => 'pending',
                'failure_reason' => 'Operator API timed out. Pending retry.',
                'next_retry_at'  => now()->addMinutes(5),
            ]);

            return response()->json([
                'message'       => 'Payment is being processed. You will be notified once confirmed.',
                'txn_id'        => $bbpsTxn->txn_id,
                'amount'        => $bbpsTxn->amount,
                'status'        => 'pending',
            ], 202);
        }

        // 4b: Explicit failure — reverse the wallet debit immediately
        $this->refundWallet($user->id, $amount, $txnId, $data['biller_name']);

        $bbpsTxn->update([
            'status'         => 'failed',
            'failure_reason' => 'Operator returned failure. Amount refunded.',
            'processed_at'   => now(),
        ]);

        return response()->json([
            'message'       => 'Bill payment failed. Amount has been refunded to your wallet.',
            'txn_id'        => $bbpsTxn->txn_id,
            'amount'        => $bbpsTxn->amount,
            'status'        => 'failed',
            'balance_after' => $bbpsTxn->balance_before, // balance restored
        ], 200);
    }

    /** GET /api/v1/bbps/history */
    public function history(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min($request->integer('per_page', 15), 100);

        $rows = BbpsTransaction::where('user_id', $user->id)
            ->when($request->filled('category'), fn ($q) => $q->where('biller_category', $request->category))
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'),fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'),  fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($rows);
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Reverse a BBPS wallet debit. Called only on confirmed API failure.
     * Uses a fresh DB::transaction so it is atomic even if called after
     * the original transaction has already committed.
     */
    private function refundWallet(int $userId, float $amount, string $txnId, string $billerName): void
    {
        try {
            DB::transaction(function () use ($userId, $amount, $txnId, $billerName) {
                $wallet = DB::table('wallets')
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    return;
                }

                $balAfter = round((float) $wallet->balance + $amount, 2);

                DB::table('wallets')->where('user_id', $userId)->update([
                    'balance'         => $balAfter,
                    'total_recharged' => DB::raw("total_recharged - {$amount}"),
                    'updated_at'      => now(),
                ]);

                DB::table('wallet_transactions')->insert([
                    'user_id'       => $userId,
                    'type'          => 'credit',
                    'amount'        => $amount,
                    'balance_after' => $balAfter,
                    'description'   => "Refund: BBPS {$billerName} failed — {$txnId}",
                    'reference'     => 'REFUND_' . $txnId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });
        } catch (\Throwable $e) {
            // Log for manual reconciliation — the transaction is already
            // marked failed so admin can process the refund manually.
            Log::critical('BBPS refundWallet failed — MANUAL REFUND REQUIRED', [
                'user_id' => $userId,
                'amount'  => $amount,
                'txn_id'  => $txnId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalise varied BBPS operator API success indicators.
     */
    private function normaliseBbpsStatus(array $response): string
    {
        $raw = strtolower(
            $response['status']   ??
            $response['STATUS']   ??
            $response['txnStatus'] ??
            $response['code']     ??
            ''
        );

        if (\in_array($raw, ['success', 'successful', '1', 'ok', 'true', 'approved'], true)) {
            return 'success';
        }

        if (\in_array($raw, ['pending', 'processing', 'initiated'], true)) {
            return 'pending';
        }

        return 'failed';
    }
}
