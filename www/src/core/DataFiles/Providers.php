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
        'viettel corporation - vietel corporation' => 'Viettel Corp.', 
        'viettel corporation - electric telecommunication company' => '(EVN)-Viettel Corp.', 
        'vtc wireless broadband company' => 'VTC', 
        'cty co phan ha tang vien thong cmc - cmc telecom infrastructure company' => 'CMC Telecommunications Services Company', 
        'vdc' => 'VNPT', 
        'vdc - vietnam posts and telecommunications(vnpt)' => 'VNPT', 
        'cong ty co phan tap doan vi na - trung tam vnnic' => 'VNNIC - Vietnam Internet Network Information Center', 
        'cmc telecom infrastructure company' => 'CMC Telecommunications Services Company', 
        'viettel corporation - ip range assign for the internet cable service in' => 'Viettel Corp.', 
        'lo vp1, phuong yen hoa, cau giay, hanoi' => 'Mobifone', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam posts and telecommunications(vnpt)' => 'VNPT', 
        'fpt telecom company' => 'FPT Telecom', 
        'vietnam posts and telecommunications (vnpt) - vietnam posts and telecommunications(vnpt)' => 'VNPT', 
        'corporation for financing and promoting technology - fpt telecom company' => 'FPT Telecom', 
        'saigon tourist cable television - saigon tourist cable televition company' => 'SCTV', 
        'sai gon postel corporation - saigon postel corporation' => 'Saigon Postel JSC', 
        'viettel - cht company ltd - vietel - cht compamy' => 'Viettel Corp.', 
        'fpt telecom - fpt telecom company' => 'FPT Telecom', 
        'vietnam posts and telecommunications (vnpt) - vietnam telecom national' => 'VNPT', 
        'netnam corporation - netnam company' => 'NetNam', 
        'netnam corporation - saigon postel corporation' => 'NetNam', 
        'dai ip dong su dung cho ket noi xdsl - fpt telecom company' => 'FPT Telecom', 
        'vdc - ftth static + ip adsl static + cable tv, voip' => 'VNPT', 
        'vietnam posts and telecommunications (vnpt)' => 'VNPT', 
        'vietnam posts and telecommunications corp (vnpt) - hochiminh city post and telecom company' => 'VNPT', 
        'quang trung software city development company' => 'Quang Trung Software City (QTSC)', 
        'vdc - vietnam posts and telecommunications (vnpt)' => 'VNPT', 
        'saigon tourist cable television' => 'SCTV', 
        'digital communications company' => 'VTC DIGICOM', 
        'viettel corporation - ip range assign for internet cable service in hcmc' => 'Viettel Corp.', 
        'the corporation for financing and promoting techno - fpt telecom company' => 'FPT Telecom', 
        'netnam - branch of netnam company in ho chi minh city' => 'NetNam', 
        'netnam corporation - branch of netnam company in ho chi minh city' => 'NetNam', 
        'viettel corporation - dai ip cho vietel mobile' => 'Viettel Corp.', 
        'vdc - cuc buu dien tw' => 'VNPT', 
        'branch of cmc telecommunications services company' => 'CMC Telecommunications Services Company', 
        'viettel corporation - dai ip cho vietel' => 'Viettel Corp.', 
        'vietnam posts and telecommunications corp (vnpt) - ho chi minh city post and telecom company' => 'VNPT', 
        'fpt online jsc' => 'FPT Telecom', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam posts and telecommunications (vnpt)' => 'VNPT', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam telecom national' => 'VNPT', 
        'hanoi telecom corporation' => 'Hanoi Telecom JSC', 
        'layer 2 -customer nework of vtdc - vietel - cht compamy' => 'Viettel Corp.', 
        'vietnam posts and telecommunications corp (vnpt) - vietnam data communication company' => 'VNPT', 
        'vtc- multimedia corporation - vtc multimedia corporation' => 'VTC', 
        'netnam corporation - ip range assign for bras in distrist 7 hcmc' => 'NetNam', 
        'vietnam posts and telecommunications (vnpt) - vietnam data communication company' => 'VNPT', 
        'fpt telecom - vung dia chi ip cap cho dich vu iptv tai hai phong' => 'FPT Telecom', 
        'fpt telecom - vung dia chi ip cap cho dich vu iptv tai ha noi' => 'FPT Telecom', 
        'viettel corporation - ip range for leased line service in hanoi' => 'Viettel Corp.', 
        'netnam corporation - broadband ethernet service' => 'NetNam', 
        'hanoi telecom corporation - hanoi telecom joint stock company - hcmc branch' => 'Hanoi Telecom JSC', 
        'branch of hanoi telecom jsc in hcmc - hanoi telecom joint stock company - hcmc branch' => 'Hanoi Telecom JSC', 
        'vtc- multimedia corporation' => 'VTC', 
        'netnam corporation - fttx service' => 'NetNam', 
        'vietnam posts and telecommunications (vnpt) - trung tam phat trien cong nghe thong tin - dhqghcm' => 'VNPT', 
        'viettel corporation' => 'Viettel Corp.', 
        'netnam corporation - nha xuat ban giao duc' => 'NetNam', 
        'branch of hanoi telecom jsc in hcmc' => 'Hanoi Telecom JSC', 
        'viettel (cambodia) pte., ltd - viettel (cambodia) pte., ltd.' => 'Viettel Cambodia Pte.', 
        'vietnam posts and telecommunications (vnpt) - bao tuoi tre ho chi minh' => 'VNPT', 
        'vdc - vietnam data communication company (vdc)' => 'VNPT', 
        'branch of hanoi telecom jsc in hcm' => 'Hanoi Telecom JSC', 
        'quang trung software city (qtsc). - quang trung software city development company' => 'Quang Trung Software City (QTSC)', 
    );
}
