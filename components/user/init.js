/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

$(function() {
    user.init();
});

var user = {

    login_form: $('#login'),
    controller: 'components/user/controller.php',
    dialog: 'components/user/dialog.php',

    //////////////////////////////////////////////////////////////////
    // Initilization
    //////////////////////////////////////////////////////////////////

    init: function() {
        this.login_form.on('submit', function(e) {
            e.preventDefault();
            user.authenticate();
        });
    },

    //////////////////////////////////////////////////////////////////
    // Authenticate User
    //////////////////////////////////////////////////////////////////

    authenticate: function() {
        $.post(this.controller + '?action=authenticate', this.login_form.serialize(), function(data) {
            parsed = jsend.parse(data);
            if (parsed != 'error') {
                // Session set, reload
                window.location.reload();
            }
        });
    },

    //////////////////////////////////////////////////////////////////
    // Logout
    //////////////////////////////////////////////////////////////////

    logout: function() {
        $.get(this.controller + '?action=logout', function() {
            window.location.reload();
        });
    },

    //////////////////////////////////////////////////////////////////
    // Open the user manager dialog
    //////////////////////////////////////////////////////////////////

    list: function() {
        $('#modal-content form')
            .die('submit'); // Prevent form bubbling
        modal.load(400, user.dialog + '?action=list');
    },

    //////////////////////////////////////////////////////////////////
    // Create User
    //////////////////////////////////////////////////////////////////

    create_new: function() {
        modal.load(400, user.dialog + '?action=create');
        $('#modal-content form')
            .live('submit', function(e) {
            e.preventDefault();
            var username = $('#modal-content form input[name="username"]')
                .val();
            var password1 = $('#modal-content form input[name="password1"]')
                .val();
            var password2 = $('#modal-content form input[name="password2"]')
                .val();
            if (password1 != password2) {
                message.error('Passwords Do Not Match');
            } else {
                $.get(user.controller + '?action=create&username=' + username + '&password=' + password1, function(data) {
                    create_response = jsend.parse(data);
                    if (create_response != 'error') {
                        message.success('User Account Created');
                        user.list();
                    }
                });
            }
        });
    },

    //////////////////////////////////////////////////////////////////
    // Delete User
    //////////////////////////////////////////////////////////////////

    delete: function(username) {
        modal.load(400, user.dialog + '?action=delete&username=' + username);
        $('#modal-content form')
            .live('submit', function(e) {
            e.preventDefault();
            var username = $('#modal-content form input[name="username"]')
                .val();
            $.get(user.controller + '?action=delete&username=' + username, function(data) {
                delete_response = jsend.parse(data);
                if (delete_response != 'error') {
                    message.success('Account Deleted')
                    user.list();
                }
            });
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Set Project Access
    //////////////////////////////////////////////////////////////////

    projects: function(username) {
        modal.load(400, user.dialog + '?action=projects&username=' + username);
        $('#modal-content form')
            .live('submit', function(e) {
            e.preventDefault();
            var username = $('#modal-content form input[name="username"]')
                .val();
            var access_level = $('#modal-content form select[name="access_level"]')
                .val();
            var projects = new Array();
            $('input:checkbox[name="project"]:checked').each(function(){
                projects.push($(this).val());
            });
            console.log(projects);
            if(access_level==0){ projects = 0; }
            // Check and make sure if access level not full that at least on project is selected
            if (access_level==1 && !projects) {
                message.error('At Least One Project Must Be Selected');
            } else {
                $.post(user.controller + '?action=project_access&username=' + username,{projects: projects}, function(data) {
                    projects_response = jsend.parse(data);
                    if (projects_response != 'error') {
                        message.success('Account Modified');
                        //user.list();
                    }
                });
            }
        });
    },

    //////////////////////////////////////////////////////////////////
    // Change Password
    //////////////////////////////////////////////////////////////////

    password: function(username) {
        modal.load(400, user.dialog + '?action=password&username=' + username);
        $('#modal-content form')
            .live('submit', function(e) {
            e.preventDefault();
            var username = $('#modal-content form input[name="username"]')
                .val();
            var password1 = $('#modal-content form input[name="password1"]')
                .val();
            var password2 = $('#modal-content form input[name="password2"]')
                .val();
            if (password1 != password2) {
                message.error('Passwords Do Not Match');
            } else {
                $.get(user.controller + '?action=password&username=' + username + '&password=' + password1, function(data) {
                    password_response = jsend.parse(data);
                    if (password_response != 'error') {
                        message.success('Password Changed');
                        modal.unload();
                    }
                });
            }
        });
    },

    //////////////////////////////////////////////////////////////////
    // Change Current Project
    //////////////////////////////////////////////////////////////////

    project: function(project) {
        $.get(user.controller + '?action=project&project=' + project);
    }

};
