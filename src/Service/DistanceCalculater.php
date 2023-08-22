<?php
//src/Service/DistanceCalculater.php
namespace App\Service;

class DistanceCalculater
{
    var $apiKey;
    var $apiUrl;
    var $origin;
    var $destinations;
    public function __construct() {
        $this->apiKey = (isset($_ENV['API_KEY']) ? $_ENV['API_KEY'] : '9eb61b6aa98a57d4201f19b0253c92aa');
        $this->apiUrl = 'http://api.positionstack.com/v1/forward';
        
        $this->origin = "Sint Janssingel 92, 5211 DA 's-Hertogenbosch, The Netherlands";
        $this->destinations = array(
            'Eastern Enterprise B.V.' => 'Deldenerstraat 70, 7551AH Hengelo, The Netherlands',
            'Eastern Enterprise' => '46/1 Office no 1 Ground Floor , Dada House , Inside dada silk mills compound, Udhana Main Rd, near Chhaydo Hospital, Surat, 394210, India',
            'Adchieve Rotterdam' => 'Weena 505, 3013 AL Rotterdam, The Netherlands',
            'Sherlock Holmes' => '221B Baker St., London, United Kingdom',
            'The White House' => '1600 Pennsylvania Avenue, Washington, D.C., USA',
            'The Empire State Building' => '350 Fifth Avenue, New York City, NY 10118',
            'The Pope' => 'Saint Martha House, 00120 Citta del Vaticano, Vatican City',
            'Neverland' => '5225 Figueroa Mountain Road, Los Olivos, Calif. 93441, USA'
        );
    }   

    public function get_addres_lat_log($address){
        $url = $this->apiUrl.'?access_key='.$this->apiKey.'&query='.$address;
        $result = $this->curl($url);
        return $result;
    }

    public function curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($output, TRUE);
        return $arr;
    }
    
    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                $distanceRes = round(($miles * 1.609344), 2).' KM';
            } else if ($unit == "N") {
                $distanceRes = round(($miles * 0.8684)).' NM';;
            } else {
                $distanceRes = round($miles, 2).' M';;
            }
            return $distanceRes;
        }
    }

    public function calculate_distances($origin, $destinations){
        $originAdd = urlencode($origin);
        $originLatLonArr = $this->get_addres_lat_log($originAdd);
        $latitudeFrom = (isset($originLatLonArr['data'][0]) ? $originLatLonArr['data'][0]['latitude'] : 0);
        $longitudeFrom = (isset($originLatLonArr['data'][0]) ? $originLatLonArr['data'][0]['longitude'] : 0);
        $distanceArr = array();
        foreach($destinations as $key=>$dest){
            $dest = urlencode($dest);
            $destLatLonArr = $this->get_addres_lat_log($dest);
            $latitudeTo = (isset($destLatLonArr['data'][0]) ? $destLatLonArr['data'][0]['latitude'] : 0);
            $longitudeTo = (isset($destLatLonArr['data'][0]) ? $destLatLonArr['data'][0]['longitude'] : 0);
            $distanceArr[$key] = $this->distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, 'K');
        }
        asort($distanceArr);
        return $distanceArr;
    }

    public function array2csv($csvArrData){
        $fileName = 'distance.csv';
        if(PHP_SAPI === 'cli'){
            $output = fopen('public/'.$fileName, 'w') or die("Can't open ".$fileName);
        }else{
            $output = fopen("php://output",'w') or die("Can't open php://output");  
            header("Content-Type:application/csv"); 
            header("Content-Disposition:attachment;filename=".$fileName); 
        }        
        foreach($csvArrData as $data) {
            fputcsv($output, $data);
        }        
        fclose($output) or die("Can't close php://output");
    }

}