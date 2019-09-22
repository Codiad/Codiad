<?php
/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License.
 * See http://opensource.org/licenses/MIT for more information. 
 * This information must remain intact.
 */
    include_once('config.php');

    class Git {
        
        public $resultArray;
        public $result;
        
        function __construct() {
            $log = file_get_contents("config.log");
            foreach(getConfig() as $name => $value) {
                $result = $this->executeCommand("git config " . $name . " " . $value);
                if ($result !== 0) {
                    $log .= "Config failed: " . $name . " " . $value . "\n";
                }
            }
            file_put_contents("config.log", $log);
        }

        public function init($path) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git init");
            if ($result === 0) {
                return true;
            } else {
                return false;
            }
        }
        
        public function cloneRepo($path, $repo, $init_submodules) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            if (!$this->checkExecutableFile()) {
                return $this->returnMessage("error","Failed to change permissions of shell program");
            }
            if (!$this->checkShellProgramExists()) {
                return $this->returnMessage("error", "Please install shell program!");
            }
            
            $program = $this->getShellProgram();
            $command = $program . ' -s "' . $path . '" -c "git clone '; 
            
            if ($init_submodules == "true") {
                $command = $command . '--recursive ';
            }
            
            $command = $command . $repo . ' ./"';
            
            if (isset($_POST['username'])) {
                $command = $command . ' -u "' . $_POST['username'] . '"';
            }
            if (isset($_POST['password'])) {
                $command = $command . ' -p "' . $_POST['password'] . '"';
            }
            $result = $this->executeCommand($command);
            return $this->parseShellResult($result, "Repository cloned!", "Failed to clone repo!");
        }
        
        public function status($path) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git status --branch --porcelain");
            if ($result !== 0) {
                return false;
            }
            return $this->parseGitStatus();
        }
        
        public function add($path, $file) {
            if (!is_dir($path)) return false;
            $cwd = getcwd();
            chdir($path);
            $result = $this->executeCommand("git add --all " . $file);
            chdir($cwd);
            if ($result === 0) {
                return true;
            } else {
                return false;
            }
        }
        
        public function commit($path, $msg) {
            if (!is_dir($path)) return false;
            chdir($path);
            if ($this->setGitSettings($path)) {
                $result = $this->executeCommand("git commit -m \"" . $msg . "\"");
                return $this->parseShellResult($result, "Changes commited", "Failed to commit changes!");
            }
            return $this->parseShellResult(-1, null, "Failed to set settings!");
        }
        
        public function getLog($repo) {
            if (!is_dir($repo)) return false;
            chdir($repo);
            $cmd = "git log --relative-date ";
            if (isset($_GET['path'])) {
                $cmd .= " -- " . $_GET['path'];
            }
            $result = $this->executeCommand($cmd);
            if ($result !== 0) {
                return false;
            }
            return $this->resultArray;
        }
        
        public function diff($repo, $path) {
            if (!is_dir($repo)) return false;
            chdir($repo);
            $result = $this->executeCommand("git status --branch --porcelain");
            if ($result !== 0) {
                return false;
            }
            $status = $this->parseGitStatus();
            if (in_array($path, $status['untracked'])) {
                $this->resultArray = $this->untrackedDiff($path);
            } else if (in_array($path, $status['modified'])) {
                $this->executeCommand('git diff ' . $path);
                array_push($this->resultArray, "\n");
            } else if (in_array($path, $status['added'])) {
                $this->executeCommand('git diff --cached ' . $path);
                array_push($this->resultArray, "\n");
            } else if (in_array($path, $status['renamed'])) {
                $this->executeCommand('git diff ' . $path);
                if ($this->result == "") {
                    $this->executeCommand('git status --branch --porcelain');
                    foreach($this->resultArray as $i => $line) {
                        if (strpos($line,$path) !== false) {
                            $name = substr($line, 2);
                            $this->resultArray = array("Renamed: " . $name . "\n");
                            break;
                        }
                    }
                } else {
                    array_push($this->resultArray, "\n");
                }
            } else if (in_array($path, $status['deleted'])) {
                $this->executeCommand('git diff -- ' . $path);
                array_push($this->resultArray, "\n");
            } else {
                return $this->returnMessage("notice", "No changes!");
            }
            foreach($this->resultArray as $index => $line) {
                $line = str_replace ("\t", "    ", $line);
                $this->resultArray[$index] = htmlentities($line);
            }
            return '{"status":"success","data":'. json_encode($this->resultArray) .'}';
        }
        
        public function checkout($repo, $path) {
            if (!is_dir($repo)) return false;
            chdir($repo);
            $result = $this->executeCommand("git status --branch --porcelain");
            if ($result !== 0) {
                return false;
            }
            $status = $this->parseGitStatus();
            $result = -1;
            if (in_array($path, $status['renamed'])) {
                foreach($this->resultArray as $i => $line) {
                    if (strpos($line,$path) !== false) {
                        $name = substr($line,2,strpos($line,"->") - 2);
                        $result = $this->executeCommand("git mv " . $path . " " . $name);
                        break;
                    }
                }
            } else {
                $result = $this->executeCommand("git checkout -- " . $path);
            }
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function getRemotes($path) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git remote");
            if ($result !== 0) return false;
            $buffer = array();
            foreach ($this->resultArray as $remote) {
                $result = $this->executeCommand("git remote show -n " . $remote);
                $buffer[$remote] = $this->result;
            }
            return $buffer;
        }
        
        public function newRemote($path, $name, $url) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git remote add " . $name . " " . $url);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function removeRemote($path, $name) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git remote rm " . $name);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function renameRemote($path, $name, $newName) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git remote rename " . $name . " " . $newName);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function checkoutRemote($path, $name, $remoteName){
            if (!is_dir($path)) return false;
            chdir($path);
            if (!$name) return false;
            $result = $this->executeCommand("git checkout -b " . $name . " " . $remoteName);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function getBranches($path) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git branch");
            $current = "";
            foreach($this->resultArray as $index => $line) {
                $array[$index] = trim($line);
                if (strpos($line, "* ") === 0) {
                    $current = substr($line, 2);
                    $array[$index] = $current;
                }
            }
            if (count($array) === 0) {
                $result = $this->executeCommand("git status --branch --porcelain");
                if ($result !== 0) {
                    return false;
                }
                $status     = $this->parseGitStatus();
                $array[0]   = $status['branch'];
                $current    = $status['branch'];
            }
            return array("branches" => $array, "current" => $current);
        }
        
        public function getRemoteBranches($path){
        	if (!is_dir($path)) return false;
            chdir($path);
            $current = "";
            $result = $this->executeCommand("git branch -a");
            foreach($this->resultArray as $index => $line) {
            	if(strpos($line,'remotes/') !== false){
            	    if(strpos($line,'/HEAD -> ') === false){
            		    $line = str_replace("remotes/","", $line);
                        $array[$index] = trim($line);
            	    }else{
            	        $current = trim(substr($line, strpos($line, '/HEAD -> ') + 8)); 
            	    }
            	}
            }
            return array("branches" => $array , "current" => $current);
        }
        
        public function newBranch($path, $name) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git branch " . $name);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function deleteBranch($path, $name) {
            if (!is_dir($path)) return false;
            chdir($path);
            if (strpos($name,'remotes/') === 0){
            	return false;
            }
            $result = $this->executeCommand("git branch -d " . $name);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function checkoutBranch($path, $name) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git checkout " . $name);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function renameBranch($path, $name, $newName) {
            if (!is_dir($path)) return false;
            chdir($path);
            if (strpos($name,'remotes/') === 0){
            	return false;
            }
            $result = $this->executeCommand("git branch -m " . $name . " " . $newName);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function merge($path, $name) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git merge " . $name);
            if ($result !== 0) {
                return false;
            } else {
                return true;
            }
        }
        
        public function push($path, $remote, $branch) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            if (!$this->checkExecutableFile()) {
                return $this->returnMessage("error","Failed to change permissions of shell program");
            }
            if (!$this->checkShellProgramExists()) {
                return $this->returnMessage("error", "Please install shell program!");
            }
            
            $program = $this->getShellProgram();
            $command = $program . ' -s "' . $path . '" -c "git push ' . $remote . ' ' . $branch . '"';
            
            if (isset($_POST['username'])) {
                $command = $command . ' -u "' . $_POST['username'] . '"';
            }
            if (isset($_POST['password'])) {
                $command = $command . ' -p "' . $_POST['password'] . '"';
            }
            if (isset($_POST['passphrase'])) {
                $command = $command . ' -k "' . $_POST['passphrase'] . '"';
            }
            $result = $this->executeCommand($command);
            return $this->parseShellResult($result, "Repository pushed!", "Failed to push repo!");
        }
        
        public function pull($path, $remote, $branch) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            if (!$this->checkExecutableFile()) {
                return $this->returnMessage("error","Failed to change permissions of shell program");
            }
            if (!$this->checkShellProgramExists()) {
                return $this->returnMessage("error", "Please install shell program!");
            }
            
            $program = $this->getShellProgram();
            $command = $program . ' -s "' . $path . '" -c "git pull ' . $remote . ' ' . $branch . '"';
            
            if (isset($_POST['username'])) {
                $command = $command . ' -u "' . $_POST['username'] . '"';
            }
            if (isset($_POST['password'])) {
                $command = $command . ' -p "' . $_POST['password'] . '"';
            }
            if (isset($_POST['passphrase'])) {
                $command = $command . ' -k "' . $_POST['passphrase'] . '"';
            }
            $result = $this->executeCommand($command);
            return $this->parseShellResult($result, "Repository pulled!", "Failed to pull repo!");
        }
                
        public function fetch($path, $remote) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            if (!$this->checkExecutableFile()) {
                return $this->returnMessage("error","Failed to change permissions of shell program");
            }
            if (!$this->checkShellProgramExists()) {
                return $this->returnMessage("error", "Please install shell program!");
            }
            
            $program = $this->getShellProgram();
            if($remote == "0"){
            	$command = $program . ' -s "' . $path . '" -c "git fetch"';
            }else{
            	$command = $program . ' -s "' . $path . '" -c "git fetch ' . $remote . '"';
            }
            if (isset($_POST['username'])) {
                $command = $command . ' -u "' . $_POST['username'] . '"';
            }
            if (isset($_POST['password'])) {
                $command = $command . ' -p "' . $_POST['password'] . '"';
            }
            if (isset($_POST['passphrase'])) {
                $command = $command . ' -k "' . $_POST['passphrase'] . '"';
            }
            $result = $this->executeCommand($command);
            return $this->parseShellResult($result, "Repository fetched!", "Failed to fetch repo!");
        }
        
        public function renameItem($path, $old_name, $new_name) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            chdir($path);
            if(!file_exists($new_name)){
                $command = "git mv " . $old_name . " " . $new_name;
                $result = $this->executeCommand($command);
                if (strpos($this->result, "fatal: not under version control") !== false) {
                    if (rename($old_name,$new_name)) {
                        return $this->returnMessage("success", "Renamed");
                    } else {
                        return $this->returnMessage("error", "Could Not Rename");
                    }
                } else {
                    return $this->parseShellResult($result, "Renamed", "Could Not Rename");
                }
            }else{
                return $this->returnMessage("error", "File Already Exists");
            }
        }
        
        public function submodule($repo, $path, $submodule) {
            if (!is_dir($repo)) return $this->returnMessage("error", "Wrong path!");
            if (!is_dir($path)) {
                if (!$this->checkExecutableFile()) {
                    return $this->returnMessage("error","Failed to change permissions of shell program");
                }
                if (!$this->checkShellProgramExists()) {
                    return $this->returnMessage("error", "Please install shell program!");
                }
                
                $program = $this->getShellProgram();
                $command = $program . ' -s "' . $repo . '" -c "git submodule add ' . $submodule . ' ' . $path . '"';
                
                if (isset($_POST['username'])) {
                    $command = $command . ' -u "' . $_POST['username'] . '"';
                }
                if (isset($_POST['password'])) {
                    $command = $command . ' -p "' . $_POST['password'] . '"';
                }
                if (isset($_POST['passphrase'])) {
                    $command = $command . ' -k "' . $_POST['passphrase'] . '"';
                }
                $result = $this->executeCommand($command);
                return $this->parseShellResult($result, "Submodule created", "Failed to create submodule");
            } else {
                return $this->returnMessage("error", "Submodule directory exists already");
            }
        }
        
        public function initSubmodule($path) {
            if (!is_dir($path)) return $this->returnMessage("error", "Wrong path!");
            if (!$this->checkExecutableFile()) {
                return $this->returnMessage("error","Failed to change permissions of shell program");
            }
            if (!$this->checkShellProgramExists()) {
                return $this->returnMessage("error", "Please install shell program!");
            }
            
            $program = $this->getShellProgram();
            $command = $program . ' -s "' . $path . '" -c "git submodule update --init"';
            
            if (isset($_POST['username'])) {
                $command = $command . ' -u "' . $_POST['username'] . '"';
            }
            if (isset($_POST['password'])) {
                $command = $command . ' -p "' . $_POST['password'] . '"';
            }
            if (isset($_POST['passphrase'])) {
                $command = $command . ' -k "' . $_POST['passphrase'] . '"';
            }
            $result = $this->executeCommand($command);
            return $this->parseShellResult($result, "Submodule initiated", "Failed to initiate submodule");
        }
        
        public function numstat($path) {
            if (file_exists($path)) {
                $dirname    = dirname($path);
                $filename   = basename($path);
                chdir($dirname);
                $result = $this->executeCommand("git status --branch --porcelain");
                if ($result !== 0) {
                    return false;
                }
                $status = $this->parseGitStatus();
                $result = -1;
                $plus   = 0;
                $minus  = 0;
                if (in_array($filename, $status['untracked'])) {
                    $file = file_get_contents($filename);
                    $file = explode("\n",$file);
                    $plus = count($file);
                    $minus = 0;
                } else {
                    $command    = "git diff --numstat " . $filename;
                    $result     = $this->executeCommand($command);
                    if ($result === 0) {
                        if ($this->result === "") {
                            $plus   = 0;
                            $minus  = 0;
                        } else {
                            $stats  = explode("\t",$this->result);
                            $plus   = $stats[0];
                            $minus  = $stats[1];
                        }
                    } else {
                        return false;
                    }
                }
                $result = array("status" => "success", "data" => array("branch" => $status['branch'], "insertions" => $plus,"deletions" => $minus));
                echo json_encode($result);
            } else {
                return $this->returnMessage("error", "File Does Not Exist");
            }
        }
        
        public function showCommit($path, $commit) {
            if (!is_dir($path)) return false;
            chdir($path);
            $result = $this->executeCommand("git show " . $commit);
            if ($result !== 0) {
                return $this->returnMessage("error", "Failed to show commit");
            } else {
                foreach($this->resultArray as $index => $line) {
                    $line = str_replace ("\t", "    ", $line);
                    $this->resultArray[$index] = htmlentities($line);
                }
                return json_encode(array("status" => "success", "data" => $this->resultArray));
            }
        }
        
        public function blame($repo, $path) {
            if (!is_dir($repo)) return false;
            chdir($repo);
            $result = $this->executeCommand("git blame --line-porcelain " . $path);
            if ($result !== 0) {
                return $this->returnMessage("error", "Failed to get git blame");
            } else {
                foreach($this->resultArray as $index => $line) {
                    $line = str_replace ("\t", "    ", $line);
                    $this->resultArray[$index] = htmlentities($line);
                }
                return json_encode(array("status" => "success", "data" => $this->resultArray));
            }
        }
        
        public function network($repo) {
            if (!is_dir($repo)) return false;
            chdir($repo);
            $cmds = array(
                "git log --date-order --branches --pretty=format:%H%n%P%n%an%n%at%n%s",
                "git show-ref --tags",
                "git show-ref --heads",
                "git rev-parse --abbrev-ref HEAD"
            );
            $results = array();
            foreach($cmds as $cmd) {
                $result = $this->executeCommand($cmd);
                if ($result !== 0 && $result !== 1) { // Ignore unclean exit
                    return false;
                }
                array_push($results, $this->resultArray);
            }
            $log = $results[0];
            $tags = $results[1];
            $heads = $results[2];
            $branch = $results[3][0];
            //Compute commits
            $log = array_chunk($log, 5);
            $this->commits = array();
            $keys = array("hash", "parents", "author", "time", "subject");
            $this->hash_to_commit = array();
            foreach ($log as $i => $commit) {
                $commit = array_combine($keys, $commit);
                $commit['computed'] = false;
                $commit['id'] = $i;
                if (strlen($commit['parents']) !== 0) {
                    $commit['parents'] = str_split(str_replace(" ", "", $commit['parents']), 40);
                } else {
                    $commit['parents'] = array();
                }
                array_push($this->commits, $commit);
                $this->hash_to_id[$commit['hash']] = $i;
            }
            //Compute refs
            $keys = array("hash", "name");
            $branch_to_hash = array();
            foreach($heads as $i => $ref) {
                $ref = str_replace("refs/heads/", "", $ref);
                $ref = str_replace(" ", "", $ref);
                $ref = str_split($ref, 40);
                $ref = array_combine($keys, $ref);
                $heads[$i] = $ref;
                $branch_to_hash[$ref['name']] = $ref['hash'];
            }
            foreach($tags as $i => $ref) {
                $ref = str_replace("refs/tags/", "", $ref);
                $ref = str_replace(" ", "", $ref);
                $ref = str_split($ref, 40);
                $ref = array_combine($keys, $ref);
                $tags[$i] = $ref;
            }
            
            //Line counter
            $this->n_lines = 0;
            $this->lineRanges = array();
            //Walk line of current branch
            $start = $branch_to_hash[$branch];
            $this->walkLines($start, $this->n_lines, true);
            //Walk lines of all branches
            foreach($heads as $head) {
                $this->walkLines($head['hash'], $this->n_lines, true);
            }
            
            //Extend ranges with parent
            foreach($this->lineRanges as $line => $range) {
                $start_id = $range['start_id'];
                $commit = $this->commits[$start_id];
                if (count($commit['parents']) > 0) {
                    $parent_hash = $commit['parents'][0];
                    $parent_id = $this->hash_to_id[$parent_hash];
                    $parent = $this->commits[$parent_id];
                    $this->lineRanges[$line]['start'] = $parent['time'];
                    $this->lineRanges[$line]['start_id'] = $parent_id;
                }
            }
            //Extend ranges with first child
            function searchForChild($parent_hash, $array) {
                foreach ($array as $key => $val) {
                    foreach ($val['parents'] as $parent) {
                        if ($parent === $parent_hash) {
                            return $key;
                        }
                    }
                }
                return null;
            }
            
            foreach($this->lineRanges as $line => $range) {
                $end_id = $range['end_id'];
                $commit = $this->commits[$end_id];
                $child_id = searchForChild($commit['hash'], $this->commits);
                if ($child_id !== null) {
                    $child = $this->commits[$child_id];
                    $this->lineRanges[$line]['end'] = $child['time'];
                    $this->lineRanges[$line]['end_id'] = $child_id;
                }
            }
            
            //Sort ranges
            function sort_ranges_by_start($a, $b) {
                return $a['start'] - $b['start'];
            }
            uasort($this->lineRanges, 'sort_ranges_by_start');
            
            //Calculate levels
            $levels = array();
            foreach($this->lineRanges as $line => $range) {
                $merged = false;
                foreach($levels as $index => $level) {
                    if ($level['end'] <= $range['start']) {
                        array_push($levels[$index]['lines'], $line);
                        $levels[$index]['end'] = $range['end'];
                        $merged = true;
                        break;
                    }
                }
                
                if (!$merged) {
                    array_push($levels,array(
                        'lines' => array($line),
                        'start' => $range['start'],
                        'end' => $range['end']
                    ));
                }
            }
            
            foreach($levels as $index => $level) {
                foreach($level['lines'] as $line) {
                    $this->lineRanges[$line]['level'] = $index;
                }
            }
            
            foreach($this->commits as $id => $c) {
                $this->commits[$id]['level'] = $this->lineRanges[$c['line']]['level'];
            }
            
            //Return
            return array('commits' => $this->commits, 'heads' => $heads, 'tags' => $tags, 'branch' => $branch, 'hash_to_id' => $this->hash_to_id, 'lines' => ++$this->n_lines, 'levels' => count($levels));
        }
        
        private function walkLines($hash, $line, $new_line) {
            //Get commit id by given hash
            $id = $this->hash_to_id[$hash];
            if ($this->commits[$id]['computed']) {
                return;
            }
            if ($new_line) {
                $this->n_lines++;
                $line = $this->n_lines;
            }
            $this->commits[$id]['line'] = $line;
            $this->commits[$id]['computed'] = true;
            
            //Ranges of lines
            $time = $this->commits[$id]['time'];
            if (!isset($this->lineRanges[$line])) {
                $this->lineRanges[$line] = array(
                    'start' => $time,
                    'start_id' => $id,
                    'end' => $time,
                    'end_id' => $id
                );
            } else {
                
                if ($time < $this->lineRanges[$line]['start']) {
                    $this->lineRanges[$line]['start'] = $time;
                    $this->lineRanges[$line]['start_id'] = $id;
                }
                if ($time > $this->lineRanges[$line]['end']) {
                    $this->lineRanges[$line]['end'] = $time;
                    $this->lineRanges[$line]['end_id'] = $id;
                }
            }
            
            foreach($this->commits[$id]['parents'] as $i => $parent) {
                if ($i == 0) {
                    //Same line
                    $this->walkLines($parent, $line, false);
                } else {
                    //New line
                    $this->walkLines($parent, $this->n_lines, true);
                }
            }
        }
        
        public function getSettings($path) {
            $settings = getJSON(CONFIG, 'config');
            if (empty($settings)) {
                $settings['username']   = $_SESSION['user'];
                $settings['email']      = "";
            }
            if (isset($settings[$path])) {
                foreach($settings[$path] as $i => $item) {
                    $settings['local_' . $i] = $item;
                }
            }
            
            return $settings;
        }
        
        public function setSettings($update, $path) {
            $settings = getJSON(CONFIG, 'config');
            
            foreach($update as $i => $item) {
                if (strlen($item) == 0) {
                    unset($update[$i]);
                    unset($settings[$i]);
                    
                    if (strpos($i, "local_") !== false) {
                        unset($settings[$path]);
                    }
                    continue;
                }
                
                if (strpos($i,"local_") !== false) {
                    if (!isset($settings[$path])) {
                        $settings[$path] = array();
                    }
                    $index = str_replace("local_","",$i);
                    $settings[$path][$index] = $item;
                    unset($settings[$i]);
                    unset($update[$i]);
                }
                
                if (isset($update[$i])) {
                    $settings[$i] = $update[$i];
                }
            }
            
            saveJSON(CONFIG, $settings, 'config');
        }
        
        private function setGitSettings($path) {
            $settings = $this->getSettings($path);
            
            $username = $settings['username'];
            if (isset($settings['local_username'])) {
                $username = $settings['local_username'];
            }
            
            $email = $settings['email'];
            if (isset($settings['local_email'])) {
                $email = $settings['local_email'];
            }
            
            if (!empty($username)) {
                $result = $this->executeCommand('git config user.name "' . $username . '"');
                if ($result !== 0) {
                    return false;
                }
            }
            if (!empty($email)) {
                $result = $this->executeCommand('git config user.email ' . $email );
                if ($result !== 0) {
                    return false;
                }
            }
            return true;
        }
        
        private function checkExecutableFile() {
            $program = $this->getShellProgram();
            if (shellProgram == "expect" || shellProgram == "empty") {
                if (!is_executable ($program)) {
                    if (!chmod($program, 0755)) {
                        return false;
                    }
                }
            }
            return true;
        }
        
        private function checkShellProgramExists() {
            if (shellProgram == "expect") {
                if (`which expect`) {
                    return true;
                }
            } else if (shellProgram == "empty") {
                if (`which empty`) {
                    return true;
                }
            } else if (shellProgram == "python") {
                if (`which python`) {
                    exec('python ./scripts/python.py --test',$output,$return_var);
                    if ($return_var === 0) {
                        return true;
                    }
                }
            }
            return false;
        }
        
        private function getShellProgram() {
            if (shellProgram == "expect") {
                return "./scripts/expect.sh";
            } else if (shellProgram == "empty") {
                return "./scripts/empty.sh";
            } else if (shellProgram == "python") {
                return "python ./scripts/python.py";
            }
        }
        
        private function returnMessage($status, $msg) {
            return '{"status":"' . $status . '","message":"' . $msg . '"}';
        }
        
        private function parseShellResult($result, $success, $error) {
            if ($result === null) {
                return $error;
            }
            if ($result === 0) {
                return $this->returnMessage("success", $success);
            } else {
                if ($result === 64) {
                    return $this->returnMessage("error", $this->result);
                } else if ($result == 3 || $result == 4) {
                    return $this->returnMessage("login_required","Login required!");
                } else if ($result == 7) {
                    return $this->returnMessage("passphrase_required", "passphrase_required");
                } else {
                    if (strpos($this->result, "fatal: ") !== false) {
                        $error = substr($this->result, strpos($this->result, "fatal: ") + strlen("fatal: "));
                    }
                    return $this->returnMessage("error", $error);
                }
            }
        }
        
        private function executeCommand($cmd) {
            $cmd = escapeshellcmd($cmd);
            //Allow #
            $cmd = str_replace("\#","#",$cmd);
            $cmd = str_replace("\(","(",$cmd);
            $cmd = str_replace("\)",")",$cmd);
            exec($cmd. ' 2>&1', $array, $result);
            $this->resultArray = $array;
            $this->getResultString();
            return $result;
        }
        
        private function getResultString() {
            $this->result = implode("<br>", $this->resultArray);
        }
        
        private function parseGitStatus() {
            $branch = "";
            $added = array();
            $deleted = array();
            $modified = array();
            $renamed = array();
            $untracked = array();
            
            foreach($this->resultArray as $line) {
                $tag = substr($line, 0, 2);
                //Branch
                if (strpos($tag, "##") !== false) {
                   $initialCommit = strpos($line, "Initial commit on");
                    if ($initialCommit !== false) {
                        $branch = substr($line, 21);
                    } else {
                        $sPos = strpos($line, " ", 3);
                        $dPos = strpos($line, "...", 3);
                        if ( ($sPos !== false) && ($dPos === false)) {
                            $branch = substr($line, 2, $sPos-2);
                        } else if ($dPos !== false) {
                            $branch = substr($line, 2, $dPos-2);
                        } else {
                            $branch = substr($line, 2);
                        }
                    }
                }
                
                if (strpos($tag, "A") !== false) {
                    //Added
                    array_push($added, substr($line, 2));
                } else if (strpos($tag, "D") !== false) {
                    //Deleted
                    array_push($deleted, substr($line, 2));
                } else if (strpos($tag, "R") !== false) {
                    //Renamed
                    $rPos = strpos($line, "->") + 2;
                    array_push($renamed, substr($line, $rPos));
                } else if (strpos($tag, "M") !== false || strpos($tag, "U") !== false) {
                    //Modified
                    array_push($modified, substr($line, 2));
                } else if (strpos($tag, "??") !== false) {
                    //Untracked
                    array_push($untracked, substr($line, 3));
                }
            }
            //Remove whitespace
            $branch = trim($branch);
            foreach($added as $index => $file) {
                $added[$index] = trim($file);
            }
            foreach($deleted as $index => $file) {
                $deleted[$index] = trim($file);
            }
            foreach($modified as $index => $file) {
                $modified[$index] = trim($file);
            }
            foreach($renamed as $index => $file) {
                $renamed[$index] = trim($file);
            }
            foreach($untracked as $index => $file) {
                $untracked[$index] = trim($file);
            }
            //Delete douple entries
            $buffer = array();
            foreach($added as $file) {
                if (!in_array($file, $modified)) {
                    array_push($buffer, $file);
                }
            }
            $added = $buffer;
            
            return array("branch" => $branch,"added" => $added, "deleted" => $deleted, 
                        "modified" => $modified, "renamed" => $renamed, "untracked" => $untracked);
        }
        
        private function untrackedDiff($path) {
            $result = array();
            if (is_dir($path)) {
                foreach(scandir($path) as $file) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    if (ereg("/$", $path) === false) {
                        $path .= "/";
                    }
                    $res = $this->untrackedDiff($path . $file);
                    foreach($res as $line) {
                        array_push($result, $line);
                    }
                }
            } else {
                $this->executeCommand('cat ' . $path);
                array_push($result, "diff --git a/". $path . " b/" . $path);
                foreach($this->resultArray as $index => $line) {
                    array_push($result, "+" . $line);
                }
                array_push($result, "\n");
            }
            return $result;
        }
    }
?>
