<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

/**
 * Providers names
 */
if (!isset($GLOBALS['Piwik_ProviderNames'])) {
    $GLOBALS['Piwik_ProviderNames'] = array(
        // France
        "wanadoo"                 => "Orange",
        "proxad"                  => "Free",
        "bbox"                    => "Bouygues Telecom",
        "bouyguestelecom"         => "Bouygues Telecom",
        "coucou-networks"         => "Free Mobile",
        "sfr"                     => "SFR", //Acronym, keep in uppercase
        "univ-metz"               => "Université de Lorraine",
        "unilim"                  => "Université de Limoges",
        "univ-paris5"             => "Université Paris Descartes",

        // US
        "rr"                      => "Time Warner Cable Internet", // Not sure
        "uu"                      => "Verizon",

        // UK
        'zen.net'                 => 'Zen Internet',

        // DE
        't-ipconnect'             => 'Deutsche Telekom',
        't-dialin'                => 'Deutsche Telekom',
        'dtag'                    => 'Deutsche Telekom',
        't-ipnet'                 => 'Deutsche Telekom',
        'd1-online'               => 'Deutsche Telekom (Mobile)',
        'superkabel'              => 'Kabel Deutschland',
        'unitymediagroup'         => 'Unitymedia',
        'arcor-ip'                => 'Vodafone',
        'kabel-badenwuerttemberg' => 'Kabel BW',
        'alicedsl'                => 'O2',
        'komdsl'                  => 'komDSL - Thüga MeteringService',
        'mediaways'               => 'mediaWays - Telefonica',
        'citeq'                   => 'Citeq - Stadt Münster',
        
        // VIETNAM
        //!! All characters of the keys need to be in lower case
        'viettel corporation - vietel corporation' => 'Viettel Corporation', 
        'viettel corporation - electric telecommunication company' => 'EVN - Viettel Corporation', 
        'vdc - vietnam posts and telecommunications(vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vdc' => '(VNPT) Vietnam Posts and Telecommunications', 
        'sai gon postel corporation - saigon postel corporation' => 'Saigon Postel JSC', 
        'cmc telecom infrastructure company' => 'CMC Telecommunications Services Company', 
        'corporation for financing and promoting technology - fpt telecom company' => 'FPT Telecom Company', 
        'vietnam posts and telecommunications (vnpt) - vietnam telecom national' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam telecom national' => '(VNPT) Vietnam Posts and Telecommunications', 
        'fpt telecom - fpt telecom company' => 'FPT Telecom Company', 
        'vdc - ftth static + ip adsl static + cable tv, voip' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam posts and telecommunications(vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'fpt telecom - vung dia chi ip cap cho dich vu iptv tai ha noi' => 'FPT Telecom Company', 
        'saigon tourist cable television - saigon tourist cable televition company' => 'SaiGon Tourist Cable Television', 
        'dai ip dong su dung cho ket noi xdsl - fpt telecom company' => 'FPT Telecom Company', 
        'vietnam posts and telecommunications (vnpt) - vietnam posts and telecommunications(vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'cty co phan ha tang vien thong cmc - cmc telecom infrastructure company' => 'CMC Telecommunications Services Company', 
        'netnam - branch of netnam company in ho chi minh city' => 'NetNam', 
        'viettel corporation - dai ip cho vietel' => 'Viettel Corporation', 
        'lo vp1, phuong yen hoa, cau giay, hanoi' => 'Mobifone', 
        'vietnam posts and telecommunications (vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications corp (vnpt) - ho chi minh city post and telecom company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vtc wireless broadband company' => 'VTC', 
        'branch of hanoi telecom jsc in hcmc - hanoi telecom joint stock company - hcmc branch' => 'Hanoi Telecom JSC', 
        'beeline home - beeline' => 'BeeLine', 
        'netnam corporation - netnam company' => 'NetNam', 
        'the corporation for financing and promoting techno - fpt telecom company' => 'FPT Telecom Company', 
        'vdc - vietnam posts and telecommunications (vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vdc - cuc buu dien tw' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications (vnpt) - vietnam data communication company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'fpt telecom - vung dia chi ip cap cho dich vu iptv tai hai phong' => 'FPT Telecom Company', 
        'viettel corporation - ip range assign for the internet cable service in' => 'Viettel Corporation', 
        'trung tam cong nghe thong tin mobifone' => 'Mobifone', 
        'netnam corporation - branch of netnam company in ho chi minh city' => 'NetNam', 
        'viettel (cambodia) pte., ltd - viettel (cambodia) pte., ltd.' => 'Viettel Cambodia Pte.', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam data communication company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'digital communications company' => 'VTC DIGICOM', 
        'cmc telecom service company' => 'CMC Telecommunications Services Company', 
        'viettel corporation - ip range assign for internet cable service in hcmc' => 'Viettel Corporation', 
        'hanoi telecom corporation' => 'Hanoi Telecom JSC', 
        'cong ty co phan tap doan vi na - trung tam vnnic' => 'VNNIC - Vietnam Internet Network Information Center', 
        'netnam corporation - saigon postel corporation' => 'Saigon Postel JSC', 
        'netnam corporation - fttx service' => 'NetNam', 
        'viettel corporation - dai ip cho vietel mobile' => 'Viettel Corporation', 
        'netnam corporation - broadband ethernet service' => 'NetNam', 
        'branch of cmc telecommunications services company' => 'CMC Telecommunications Services Company', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam posts and telecommunications (vnpt)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications corp (vnpt) - hochiminh city post and telecom company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'viettel corporation - ip range for leased line service in hanoi' => 'Viettel Corporation', 
        'cmc telecom service company - cmc telecommunications services company' => 'CMC Telecommunications Services Company', 
        'vimpelcom - beeline-moscow gprs firewall' => 'BeeLine', 
        'vietnam posts and telecommunications (vnpt) - ho chi minh city post and telecom company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'viettel - cht company ltd - vietel - cht compamy' => 'Viettel Corporation', 
        'viettel (cambodia) pte., ltd - #15e2, preah norodom blvd' => 'Viettel Cambodia Pte.', 
        'vimpelcom - beeline' => 'BeeLine', 
        'telecommunication service - telecommunication service' => 'Telecommunication Service - Isp in Lao', 
        'branch of hanoi telecom jsc in hcm' => 'Hanoi Telecom JSC', 
        'viettel corporation - xdsl services of hanoi' => 'Viettel Corporation', 
        'viettel perú s.a.c. - perÚ s.a.c.' => 'Viettel Corporation', 
        'branch of hanoi telecom jsc in hcmc' => 'Hanoi Telecom JSC', 
        'hanoi telecom corporation - hanoi telecom joint stock company - hcmc branch' => 'Hanoi Telecom JSC', 
        'netnam corporation - hosting service' => 'NetNam', 
        'quang trung software city development company' => 'Quang Trung Software City (QTSC)', 
        'viettel corporation - dai ip cho cong ty pungkook' => 'Viettel Corporation', 
        'viettel corporation - dai ip cho server cua khach hang' => 'Viettel Corporation', 
        'quang trung software city (qtsc). - quang trung software city development company' => 'Quang Trung Software City (QTSC)', 
        'vtc- multimedia corporation - vtc multimedia corporation' => 'VTC', 
        'the corporation for financing and promoting techno - dai ip cap cho fpt telecom' => 'FPT Telecom Company', 
        'viettel corporation - american embassy in ho chi minh city' => 'Viettel Corporation', 
        'vietnam posts and telecommunications (vnpt) - unilever company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'layer 2 -customer nework of vtdc - vietel - cht compamy' => 'Viettel Corporation', 
        'mobifone global jsc' => 'Mobifone', 
        'beeline home - vimpelcom' => 'BeeLine', 
        'branch of cmc telecommunications services company - branch of cmc telecom at hcmc' => 'CMC Telecommunications Services Company', 
        'fpt online jsc' => 'FPT Telecom Company', 
        'vdc - vietnam data communication company (vdc)' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam internet network information center (vnnic - vietnam internet network information center' => 'VNNIC - Vietnam Internet Network Information Center', 
        'vtc- multimedia corporation' => 'VTC', 
        'netnam corporation - cong ty co phan tu van va phat trien nguon nhan lu' => 'NetNam', 
        'mobifone global jsc - vnpt global investments joint-stock company' => '(VNPT) Vietnam Posts and Telecommunications', 
        'vietnam posts and telecommunications (vnpt) - trung tam phat trien cong nghe thong tin - dhqghcm' => '(VNPT) Vietnam Posts and Telecommunications', 
        'viettel-cht company ltd' => 'Viettel Corporation', 
        'netnam corporation - contracting and organizations research institute' => 'NetNam', 
        'netnam corporation - nha xuat ban giao duc' => 'NetNam', 
        'netnam corporation - ip range assign for bras in distrist 7 hcmc' => 'NetNam', 
        'viettel corporation - economic department - ministry of defense' => 'Viettel Corporation', 
        'netnam - netnam company' => 'NetNam', 
    );
}
