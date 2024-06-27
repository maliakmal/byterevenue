<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UrlShortener;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $domains = ['https://www.clickconnect.click','https://www.trendytarget.click','https://www.instantimpact.click','https://www.quickreach.click','https://www.fastforward.click','https://www.promotepower.click','https://www.reachoutnow.click','https://www.brandboost.click','https://www.directlink.click','https://www.engageclick.click','https://www.toptrends.click','https://www.clickdrive.click','https://www.reachzone.click','https://www.engagehub.click','https://www.smartlink.click','https://www.clickgenius.click','https://www.marketmove.click','https://www.clickaction.click','https://www.connectdirect.click','https://www.trendsetter.click']    ;
        foreach($domains as $domain):
            UrlShortener::firstOrCreate(['name'=>$domain, 'endpoint'=>$domain]);
        endforeach;
    }
}
