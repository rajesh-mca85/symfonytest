<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DistanceCalculater;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(DistanceCalculater $distanceCalculater): Response
    { 
        $distanceArr = $distanceCalculater->calculate_distances($distanceCalculater->origin, $distanceCalculater->destinations);
        //echo "<pre>"; print_r($distanceArr); 
        $br = (PHP_SAPI === 'cli' ? '' : '<br>');        
        $i=0; 
        $csvArrData = array(); 
        $csvArrData[] = array("Sortnumber", "Distance", "Name", "Address"); 
        $displayInfo =  'Sortnumber,  Distance,  Name,  Address'.PHP_EOL.$br;
        foreach($distanceArr as $name=>$distance){
            $i++;
            $displayInfo .= $i.', "'.$distance.'"'.', "'.$name.'"'.', "'.$distanceCalculater->destinations[$name].'"'.PHP_EOL.$br;
            $csvArrData[] = array($i, $distance, $name, $distanceCalculater->destinations[$name]);
        }
        $distanceCalculater->array2csv($csvArrData);
        if(PHP_SAPI === 'cli'){
            echo $displayInfo;    
        }     
        exit;      
        // return $this->render('home/index.html.twig', [
        //     'controller_name' => 'HomeController',
        // ]);
    }
}
