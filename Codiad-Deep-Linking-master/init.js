/*
 *  Version 0.11
 *  Based on https://github.com/Codiad/Codiad/issues/360
 */

(function(global, $){
    
    // Define core
    var codiad = global.codiad,
        scripts= document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    codiad.deepLinking = {
        
        // Allows relative `this.path` linkage
        path: curpath,
		hashNavigationBlocked: false, //set to true to ignore the hash changes
		fullPath: '',
		parents: '#project-root',
		currFolders: [],
		loadedIndex: 0,
		pathLoadingInterval: 0,
		safetyBreak: 20, //prevent infinite loops
		
        init: function() {
			//set event and move to path if the hash is already set.
			$(window).on('hashchange', codiad.deepLinking.hashChanged);
			if(location.hash != "") codiad.deepLinking.hashChanged();
			//sync the hash on the viewed elements
			$('a.file, a.directory').live('dblclick', codiad.deepLinking.updateHash);
			$('#tab-list-active-files a.label, #tab-list-active-files a.close, #dropdown-list-active-files li>a, #dropdown-list-active-files span.label').live('click', codiad.deepLinking.updateHash);
			$('#tab-close a').live('mouseup', function(){
				location.hash = "";
			});
        },
		popupPath: function()
		{
			if($('.context-menu-active').length > 0)
			{
				projectPath = $('#project-root').data('path');
				activeProject = $('#project-list li[ondblclick$="' + projectPath + '\');"]').text();
				activePath = $('.context-menu-active').attr('data-path').replace(projectPath, activeProject);
				codiad.modal.load(450, path.replace('/init.js', '') + '/dialog.php?path=' + escape(location.protocol + '//' + location.host + location.pathname + '#' + activePath));
			}
		},
		updateHash: function(event)
		{
			elem = event.currentTarget;
			codiad.deepLinking.hashNavigationBlocked = true;
			pathAttr = 'data-path';
			if ($(elem).parents('#file-manager').length == 0)
			{
				elem = $('#tab-list-active-files .active a').get(0);
				pathAttr = 'title';
			}
			if($(elem).length > 0)
			{
				activePath = $(elem).attr(pathAttr).split('/');
				projectPath = activePath[0];
				if (projectPath == '')
				{
					for(i=1; i<activePath.length; i++)
					{
						projectPath = projectPath + '/' + activePath[i];
						activeProj = $('#project-list li[ondblclick$="' + projectPath + '\');"]');
						if (activeProj.length > 0)
						{
							break;
						}
					}
				}
				else
				{
					activeProj = $('#project-list li[ondblclick$="' + projectPath + '\');"]');
				}

				location.hash = '#' + $(elem).attr(pathAttr).replace(projectPath, activeProj.text());
			}
			setTimeout(function(){
				if($('#tab-list-active-files .active a').length == 0)
				{
					location.hash = "";
				}
				codiad.deepLinking.hashNavigationBlocked = false;
			}, 500);
		},
		hashChanged: function()
		{
			if(location.hash.substring(0,1) == "#" && !codiad.deepLinking.hashNavigationBlocked)
			{
				codiad.deepLinking.fullPath = location.hash.substring(1);
				if(codiad.deepLinking.fullPath != "")
				{
					$('#modal-overlay').show();
					clearTimeout(codiad.deepLinking.pathLoadingInterval);
					//reset vars
					codiad.deepLinking.currFolders = codiad.deepLinking.fullPath.split('/');
					codiad.deepLinking.parents = '#project-root';
					codiad.deepLinking.safetyBreak = 20;
					codiad.deepLinking.loadedIndex = 0;
					//start loading path
					codiad.deepLinking.pathLoadingInterval = setInterval(codiad.deepLinking.loadPath, 500);
				}
			}
		},
		loadPath: function()
		{
			codiad.deepLinking.safetyBreak = codiad.deepLinking.safetyBreak -1;
			// chech if the path if fully loaded or if we need to break
			if (codiad.deepLinking.safetyBreak<0 || codiad.deepLinking.loadedIndex == codiad.deepLinking.currFolders.length)
			{
				clearTimeout(codiad.deepLinking.pathLoadingInterval);
				$('#modal-overlay').hide();
				return;
			}

			if(codiad.deepLinking.loadedIndex == 0)
			{
				//the root of the path is the project. is loaded different than the directories.
				if($('#project-root').text() != codiad.deepLinking.currFolders[0])
				{
					$('#project-list li:contains(' + codiad.deepLinking.currFolders[0] + ')').dblclick();
					codiad.deepLinking.parents = codiad.deepLinking.parents + ':contains(' + codiad.deepLinking.currFolders[0] + ')';
				}
			}
			else
			{
				//get the parent path
				parentsSplit = codiad.deepLinking.parents.split('~ ul');
				currParent = $(parentsSplit[0]);
				for(i = 1; i<parentsSplit.length; i++)
				{
					currParent = currParent.siblings('ul').find(parentsSplit[i]);
				}

				//check if its open
				if(currParent.hasClass('open'))
				{
					linkType = 'directory';
					if(codiad.deepLinking.loadedIndex == codiad.deepLinking.currFolders.length - 1)
					{
						if(codiad.deepLinking.currFolders[codiad.deepLinking.loadedIndex].indexOf('.')!=-1)
						{
							//if it is the last and ther is a point in the name... is probably a file
							linkType = 'file';
						}
					}
					//move one level on the path
					codiad.deepLinking.parents = codiad.deepLinking.parents + ' ~ ul .' + linkType + '[data-path$="' + codiad.deepLinking.currFolders[codiad.deepLinking.loadedIndex] + '"]';
					parentsSplit = codiad.deepLinking.parents.split('~ ul');
					currParent = $(parentsSplit[0]);
					for(i = 1; i<parentsSplit.length; i++)
					{
						currParent = currParent.siblings('ul').find(parentsSplit[i]);
					}
					//check if the new parent is not open
					if(!currParent.hasClass('open'))
					{
						currParent.parent().children('span').click();
					}
					else
					{
						//move the index level of the path
						codiad.deepLinking.loadedIndex = codiad.deepLinking.loadedIndex + 1;
						codiad.deepLinking.safetyBreak = 20;						
						codiad.deepLinking.loadPath();
						return;
					}
				}
				else
				{
					//if it is not open, wait a little more.
					//this could be better implemented with callbacks on the filetree....
					return;
				}
			}
			//move the index level of the path
			codiad.deepLinking.loadedIndex = codiad.deepLinking.loadedIndex + 1;
			codiad.deepLinking.safetyBreak = 20;
			codiad.deepLinking.pathLoadingInterval = setTimeout(codiad.deepLinking.loadPath, 500);
		}

    };

    // Instantiates plugin
    $(function() {    
        codiad.deepLinking.init();
    });
	
})(this, jQuery);
