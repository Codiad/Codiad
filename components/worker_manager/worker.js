importScripts('../../lib/diff_match_patch.js');

tasks = {
    diff: function(config){
        var dmp = new diff_match_patch();
        var patchTxt = dmp.patch_toText(dmp.patch_make(config.original, config.changed));
        return {
            success: true,
            result: patchTxt
        };
    }
}

self.addEventListener('message', function(e){
    var config = e.data;
    var outcome = tasks[config.taskType](config);
    self.postMessage(outcome);
}, false);
