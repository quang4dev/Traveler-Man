<?php

header('Content-type: text/plain');

/**
 * Class City
 */
class City
{
    public $name;
    public $longitude;
    public $latitude;

    public function __construct($name, $latitude, $longitude)
    {
        $this->name         = $name;
        $this->latitude     = $latitude;
        $this->longitude    = $longitude;
    }
}

/**
 * Class ShortestDistance
 * Get shortest distance
 */
class ShortestDistance
{
    protected $n                = 0;
    protected $locations        = array();
    protected $costMatrix       = array();
    protected $cities           = [];

    private $shortestRoutes     = [];
    private $shortestDistance   = 0;

    /**
     * Solve the problem
     */
    public function solve()
    {
        $this->cities   = array_reverse($this->cities);
        $currentCity    = null;
        $firstCity      = array_pop($this->cities);
        while (!empty($this->cities)) {
            $currentCity        = $currentCity ?: $firstCity;
            $shortestDistance   = INF;
            $shortestRoute      = [];
            foreach ($this->cities as $key => $city) {
                $distant = round($this->distance($currentCity, $city), 2);
                if ($shortestDistance > $distant) {
                    $shortestDistance   = $distant;
                    $shortestRoute      = [$currentCity, $city, 'distant' => $distant];
                    $nearestCity        = $city;
                    $index              = $key;
                }
            }
            if (isset($index) && isset($nearestCity)) {
                unset($this->cities[$index]);
                $currentCity            = $nearestCity;
                $this->shortestRoutes[] = $shortestRoute;
                $this->shortestDistance += $shortestDistance;
            } else {
                break;
            }
        }
        // back to first city
        if ($currentCity) {
            $distant                = round($this->distance($currentCity, $firstCity), 2);
            $this->shortestRoutes[] = [$currentCity, $firstCity, 'distant' => $distant];
            $this->shortestDistance += $distant;
        }
    }

    /**
     * Add City
     * @param $name
     * @param $latitude
     * @param $longitude
     */
    public function addCity($name, $latitude, $longitude)
    {
        $this->cities[] = new City($name, $latitude, $longitude);
    }

    /**
     * Return Routes
     * @return string
     */
    public function getShortestRoutes()
    {
        $routes = [];
        foreach ($this->shortestRoutes as $route) {
            $routes[] = $route[0]->name;
        }
        return implode("\n", $routes);
    }

    /**
     * Return Distance
     * @return int
     */
    public function getShortestDistance()
    {
        return $this->shortestDistance;
    }

    /**
     * Code copied from https://www.geodatasource.com/developers/php
     *
     * @param City $currentCity
     * @param City $city
     * @param string $unit
     * @return float|int
     */
    private function distance($currentCity, $city, $unit = 'M')
    {
        $lat1 = $currentCity->latitude;
        $lon1 = $currentCity->longitude;
        $lat2 = $city->latitude;
        $lon2 = $city->longitude;
        if ($lat1 == $lat2 && $lon1 == $lon2) {
            return 0;
        }
        $theta  = $lon1 - $lon2;
        $dist   =
            sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist   = acos($dist);
        $dist   = rad2deg($dist);
        $miles  = $dist * 60 * 1.1515;
        $unit   = strtoupper($unit);


        if ($unit == "K") {
            return ($miles * 1.609344);
        } elseif ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

try {
    $shortestDistance = new ShortestDistance();

    $cities = fopen("cities.txt", "r") or die("Unable to open file!");
    // Output one line until end-of-file
    while (!feof($cities)) {
        $city       = fgets($cities);
        $location   = explode(" ", $city);
        $longitude  = array_pop($location);
        $latitude   = array_pop($location);
        $longitude  = trim(str_replace(array("\n", "\r"), ' ', $longitude));
        $shortestDistance->addCity(implode(" ", $location), (double)$latitude, (double)$longitude);
    }
    fclose($cities);

    $shortestDistance->solve();
    echo $shortestDistance->getShortestRoutes();
//    echo "\nTotal distance: " . $shortestDistance->getShortestDistance() . "\n\n";
} catch (Exception $e) {
    echo $e;
    exit;
}
