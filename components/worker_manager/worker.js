importScripts('../../lib/diff_match_patch.js');

tasks = {
    diff: function(config){
        var dmf = new diff_match_patch();
        var patchTxt = dmf.patch_toText(dmf.patch_make(config.original, config.changed));
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
