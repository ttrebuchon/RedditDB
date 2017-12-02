

String.prototype.replaceAll = function(orig, replace)
{
    let str = this;
    let tmp = null;
    while ((tmp = str.replace(orig, replace)) != str)
    {
        str = tmp;
    }
    return tmp;
};



function obliterateSubs()
{
    let input = document.getElementById('obliterate_subreddits');
    let subsStr = input.value;

    subsStr = subsStr
        .toLowerCase()
        .replaceAll('\r\n', ';')
        .replaceAll('\n', ';')
        .split(' ')
        .join('')
        .replaceAll('/r/', '')
        .replaceAll('/r', '')
        .replaceAll('r/', '');
    

    let subs = subsStr.split(';');

    subs = subs.filter(function(str) {return str != 'all' && str != ''; });
    subs = subs.filter(function(str, index) {
        return subs.indexOf(str) == index;
    });

    if (subs.length == 0)
    {
        input.value = '';
        return;
    }

    input.value = JSON.stringify(subs);

    document.forms.ObliterateSubsForm.submit();
}

function obliteratePosts()
{
    let input = document.getElementById('obliterate_posts');
    let postsStr = input.value;

    postsStr = postsStr
        .toLowerCase()
        .replaceAll('\r\n', ';')
        .replaceAll('\n', ';')
        .split(' ')
        .join('');

    let posts = postsStr.split(';');

    posts = posts.filter(function(str) {return str != ''; });
    posts = posts.filter(function(str, index) {
        return posts.indexOf(str) == index;
    });

    if (posts.length == 0)
    {
        input.value = '';
        return;
    }

    input.value = JSON.stringify(posts);

    document.forms.ObliteratePostsForm.submit();
}

function updateUser()
{
    let userIn = document.getElementById('update_user_username');
    let username = userIn.value = userIn.value.replaceAll(' ', '');

    if (username === '')
    {
        return;
    }

    if (username.toLowerCase() === 'admin')
    {
        userIn.value = '';
        return;
    }

    document.forms.UserPermissionsForm.submit();
}

function backupDB()
{
    let elem = document.getElementById('backup_db_action');
    elem.value = 1;
    document.forms.BackupDBForm.submit();
}