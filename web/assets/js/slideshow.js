// JavaScript Document


        function start_timer() {         
          
           window.setInterval(function () { slideshow() }, 30000);         
            
                                                                                                                                  
        }
        
           
        //Creates a timer to measure duration of game
        function slideshow () {
            
          var link = ($('#random a.img'));
                      
          window.location.href = link[0] 
                                                                                                                                                                                                                                           
        }
        
        $(document).ready(function () { 
            start_timer();
             $('#random').css('visibility', 'hidden');
        
        });
        
