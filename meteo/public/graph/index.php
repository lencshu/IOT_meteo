<!DOCTYPE HTML>  
<html>  


      
    <head>  
        <meta http-equiv="Content-Type" content="text/html; charset=GBK">  
        <title> Meteo </title>  
        <script type="text/javascript" src="http://cdn.hcharts.cn/jquery/jquery-1.8.3.min.js"></script>
        <script type="text/javascript">  
            $(function() {  
                $('#container').highcharts({  
                    chart: {  
                        type: 'spline',  
                        marginRight: 150,  
                        marginLeft: 150,  
                        marginBottom: 25,  
                        animation: Highcharts.svg,  
                          
                        events: {  
                            load: function() {  
  
                                 
                                var series_vitesse  = this.series[0];  
                                var series_direction   = this.series[1];  
                                var series_temperature = this.series[3]
                               
                                setInterval(function() {  
  
                                 
                                    jQuery.getJSON('/api/meteo', null,  
                                    function(data) {  
  
                                   
                                        var x = (new Date()).getTime();  
  
                                    
                                        var vitesse      = vitesse;  
                                        var direction     = direction;  
                                        var temperature  = temperature;  
  
                            
                                        series_vitesse.addPoint([x, vitesse], true, true);  
                                        series_direction.addPoint([x, direction], true, true);
                                        series_temperature.addPoint([x, temperature], true, true);  
                                    });  
                                },  
                                1000  
                                );  
                            }  
                        }  
                    },  
                    title: {  
                        text: 'value',  
                        x: -20  
                    },  
                    xAxis: {  
                        type: 'datetime',  
                        tickPixelInterval: 150  
                    },  
                    yAxis: {  
                        title: {  
                            text: 'value'  
                        },  
                        plotLines: [{  
                            value: 0,  
                            width: 1,  
                            color: '#808080'  
                        }]  
                    },  
                    tooltip: {  
                        valueSuffix: ''  
                    },  
                    legend: {  
                        layout: 'vertical',  
                        align: 'right',  
                        verticalAlign: 'top',  
                        x: -10,  
                        y: 100,  
                        borderWidth: 0  
                    },  
                    series: [  
        
                    {  
                        name: 'Vitesse (m/s)',  
                        data: (function() {  
                            var data = [],  
                            time = (new Date()).getTime(),  
                            i;  
  
                          
                            for (i = -9; i <= 0; i++) {  
                                data.push({  
                                    x: time + i * 1000,  
                                    vitesse: 0  
                                });  
                            }  
                            return data;  
                        })()  
                    },  
  
             
                    {  
                        name: 'Direction (360°)',  
                        data: (function() {  
                            var data = [],  
                            time = (new Date()).getTime(),  
                            i;  
  
             
                            for (i = -9; i <= 0; i++) {  
                                data.push({  
                                    x: time + i * 1000,  
                                    y: 0  
                                });  
                            }  
                            return data;  
                        })()  
                    },  
                    {  
                        name: 'Temperature (℃)',  
                        data: (function() {  
                            var data = [],  
                            time = (new Date()).getTime(),  
                            i;  
  
                            for (i = -9; i <= 0; i++) {  
                                data.push({  
                                    x: time + i * 1000,  
                                    y: 0  
                                });  
                            }  
                            return data;  
                        })()  
                    },  
                    ]  
                });  
            });  
        </script>  
        <style>  
            html,body { margin:0px; height:100%; }  
        </style>  
    </head>  
      
    <body>  
        <script src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script> 
        <script src="http://cdn.hcharts.cn/highcharts/modules/exporting.js"></script>
        <div id="container" style="min-width: 400px; height: 80%; margin: 0 auto">  
        </div>  
    </body>  
  
</html>  
