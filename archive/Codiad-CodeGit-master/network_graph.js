/*
 * Copyright (c) Andr3as, Gitlab.org, distributed
 * as-is and without warranty under the MIT License.
 * See http://opensource.org/licenses/MIT for more information. 
 * This information must remain intact.
 *
 * Bases on branch_graph.js
 * https://gitlab.com/gitlab-org/gitlab-ce/blob/f49868adf1a2ea24815d432640cd0d996e0d87a0/app/assets/javascripts/network/branch_graph.js
 * MIT License: https://gitlab.com/gitlab-org/gitlab-ce/blob/32da7602686f2b8161175d82b121deb9e01b2db5/LICENSE
 */

codiad.CodeGit.network_graph = {
    
    branch: "",
    commits: [],
    element: "git_network",
    hash_to_id: [],
    heads: [],
    lines: 0,
    levels: 0,
    tags: [],
    
    colors: [],
    offsetSubject: -1,
    offsetDot: -1,
    offsetX: 100,
    offsetY: 20,
    unitTime: 20,
    unitSpace: 20,
    
    generate: function() {
        this.prepare();
        this.collectColors();
        this.draw();
    },
    
    draw: function() {
        var subject, level, color, x, y, commit, hash, parent_hash, parent_id, parent, parent_x, parent_y, path;
        var fn_click = function() {
            hash = this.data('hash');
            codiad.CodeGit.showCommit(codiad.CodeGit.location, hash);
        };
        
        for (var i = 0; i < this.commits.length; i++) {
            commit = this.commits[i];
            subject = commit.subject;
            y = this.offsetY + i * this.unitTime;
            
            this.paper.text(this.offsetSubject, y, subject)
                .attr({
                    "text-anchor": "start",
                    fill: "#fff",
                    font: "14px Ubuntu",
                    cursor: "pointer"
                })
                .click(fn_click)
                .data("hash", commit.hash);
            
            level = commit.level;
            color = this.colors[commit.level];
            x = this.offsetDot - level * this.unitSpace;
            this.paper.circle(x, y + 2, 3)
                .attr({
                    fill: color,
                    stroke: "none",
                    cursor: "pointer"
                })
                .click(fn_click)
                .data("hash", commit.hash);
            
            for (var j = 0; j < commit.parents.length; j++) {
                parent_hash = commit.parents[j];
                parent_id = this.hash_to_id[parent_hash];
                parent = this.commits[parent_id];
                
                parent_x = this.offsetDot - parent.level * this.unitSpace;
                parent_y = this.offsetY + parent_id * this.unitTime;
                
                path = "M" + x + " " + (y + 2);
                if (x != parent_x) {
                    if (j === 0) {
                        path += "L" + x + " " + (parent_y - this.unitTime / 2);
                    } else {
                        path += "L" + parent_x + " " + (y + this.unitTime / 2);
                    }
                }
                path += "L" + parent_x + " " + (parent_y + 2);
                
                //Merge
                if (j > 0) {
                    color = this.colors[parent.level];
                }
                
                this.paper.path(path).toBack().attr({
                    stroke: color
                });
            }
        }
        
        var labels = this.heads, seen = {}, id, name, text, textbox, rect, triangle, label;
        //http://stackoverflow.com/a/30026006
        //Tags are currently missing since tags have an indepentent hash wich does not refer to the commit
        for (i = 0; i < labels.length; i++) {
            id = this.hash_to_id[labels[i].hash];
            name = labels[i].name;
            commit = this.commits[id];
            x = this.offsetDot - commit.level * this.unitSpace;
            y = this.offsetY + id * this.unitTime;
            // Truncate if longer than 17 chars
            if (name.length > 17) {
                name = name.substr(0, 15) + "…";
            }
            text = this.paper.text(x + 4, y + 2, name).attr({
                "text-anchor": "start",
                font: "10px Ubuntu",
                fill: "#000",
                title: name
            });
            textbox = text.getBBox();
            // Create rectangle based on the size of the textbox
            rect = this.paper.rect(x, y - 7, textbox.width + 5, textbox.height + 5, 4).attr({
                fill: "#fff",
                stroke: "none"
            });
            triangle = this.paper.path(["M", x - 5, y + 2, "L", x - 15, y - 2, "L", x - 15, y + 6, "Z"]).attr({
                fill: "#fff",
                stroke: "none"
            });
            label = this.paper.set(rect, text);
            label.transform(["t", -rect.getBBox().width - 15, 0]);
            // Set text to front
            text.toFront();
        }
    },
    
    prepare: function() {
        var ch, cw, gh,gw;
        gh = $(this.element).height();
        gw = $(this.element).width();
        ch = Math.max(gh, this.offsetY + this.unitTime * this.commits.length);
        cw = Math.max(gw, this.offsetX + this.unitSpace + 600);
        this.paper = Raphael(this.element, cw, ch);
        // Calculate offsets
        this.offsetDot = this.offsetX + this.levels * this.unitSpace;
        this.offsetSubject = this.offsetDot + 20;
        
    },
    
    collectColors: function() {
        Raphael.getColor.reset();
        this.colors = [];
        for (var i = 0; i < this.lines; i++) {
            this.colors.push(Raphael.getColor(0.8));
            // Skipping a few colors in the spectrum to get more contrast between colors
            Raphael.getColor();
            Raphael.getColor();
        }
    },
    
    setData: function(data) {
        this.branch = data.branch;
        this.commits = data.commits;
        this.heads = data.heads;
        this.lines = data.lines;
        this.levels = data.levels;
        this.tags = data.tags;
        this.hash_to_id = data.hash_to_id;
    }
};