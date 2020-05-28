# Project Title

Route Map Larvel Nova Field (Buil on top of Here Maps Api)

### Installing
 

composer require haythem/route-map-field


## Test
        use Haythem\RouteMapField\RouteMapField;


        $points = [
            '{"lat":"-34.1380995","lon":"18.3566494","description":"Office"}',
            '{"lat":"-34.12683310","lon":"18.38998250","description":"Candice Douglas (INV - FF0607)"}',
            '{"lat":"-34.07817000","lon":"18.43703700","description":"Jurgen Kerschbaumer (INV - FF0599)"}',
            '{"lat":"-34.07704120","lon":"18.45559830","description":"Tamsen Barrett (INV - FF0595)"}',
            '{"lat":"-34.07897430","lon":"18.46030930","description":"Gemma Rader (INV - FF0597)"}',
            '{"lat":"-34.08988480","lon":"18.46012480","description":"Ilke Kemp (INV - FF0596)"}',
            '{"lat":"-34.06624000","lon":"18.43453000","description":"Lisa Vosloo (INV - FF0594)"}',
            '{"lat":"-34.04468320","lon":"18.44628980","description":"Nic Wides (INV - FF0598)"}',

            ];
            
        //minimum number of points is two

                    RouteMapField::make('route')
                      ->gpsPoints($points)
                      ->apikey('mEdMOJyTw4MSyoo_buAPWngRK0bmd_qxVx5sonBLz2c')
                      //->svgMarker('')
                      ->showSummary(true),
                      //->showManuevers(true),


## Result

![](result.png)