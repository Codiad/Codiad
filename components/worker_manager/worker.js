tasks = {
    diff: function(config){
        return {
            success: true,
            result: "<<Some dummy diff>>"
        };
    }
}

self.addEventListener('message', function(e){
    var config = e.data;
    var outcome = tasks[config.taskType](config);
    self.postMessage(outcome);
}, false);