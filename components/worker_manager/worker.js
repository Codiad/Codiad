self.addEventListener('message', function(e){
    self.postMessage({
        success: true,
        result: "<<Some dummy diff>>"
    });
}, false);