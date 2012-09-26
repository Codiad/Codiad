/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

$(function(){ poller.init(); });

var poller = {
    
    controller : 'components/poller/controller.php',
    interval : 10000,
    
    init : function(){
    
        poller.check_auth();
        
    },
    
    //////////////////////////////////////////////////////////////////
    // Poll authentication
    //////////////////////////////////////////////////////////////////
    
    check_auth : function(){
        
        setInterval(function(){
        
            // Run controller to check session (also acts as keep-alive)
            $.get(poller.controller+'?action=check_auth',function(data){
                
                if(data){
                    parsed = jsend.parse(data);
                    if(parsed!='error'){
                        // Session not set, reload
                        window.location.reload();
                    }
                }
                
            });
            
            // Check user
            $.get(user.controller+'?action=verify',function(data){
                if(data=='false'){ user.logout(); }
            });
        
        },poller.interval);
        
    }
    
};