(function(global, $){

    codiad.workerManager = {
        taskQueue: [],
        worker: null,
        addTask: function(taskConfig, callback, context){
            var _this = this;
            this.taskQueue.push({
                config: taskConfig,
                callback: callback,
                context: context
            });

            this.clearSubsidableTasks(taskConfig.id);

            if (! this.workerRunning()) {
                var initStatus = this.initiateWorker();
                if (! initStatus) {
                    callback(null, false);
                    return;
                }
                this.worker.addEventListener('message', function(e){
                    _this.concludeTask(e.data);
                }, false);
            }
            this.scheduleNext();
        },
        workerRunning: function(){
            return !! this.worker;
        },
        initiateWorker: function(){
            this.worker = new Worker('components/worker_manager/worker.js');
            return !! this.worker;
        },
        clearSubsidableTasks: function(id){
            var i = this.taskQueue.length -2;
            while(i > 0) {
                if (this.taskQueue[i].id == id) {
                    this.taskQueue.splice(i, 1);
                }
                i--;
            }
        },
        scheduleNext: function(){
            var taskConfig = this.taskQueue[0].config;
            this.worker.postMessage(taskConfig);
        },
        concludeTask: function(msg){
            var tq = this.taskQueue[0];
            callback = tq.callback;
            context = tq.context;
            this.taskQueue.splice(0, 1);
            if (this.taskQueue.length > 0) {
                this.scheduleNext();
            }
            tq.callback.apply(context, [msg.success, msg.result]);
        }
    }

})(this, jQuery);