
function watch(type)
{
    let elem = document.getElementById('Watch_' + type + '_Action');
    elem.value = 1;
}


function watchSubreddits()
{
    watch('Subreddits');
    document.forms.DataForm.submit();
}

function watchUsers()
{
    watch('Users');
    document.forms.DataForm.submit();
}

function watchPosts()
{
    watch('Posts');
    document.forms.DataForm.submit();
}

function watchComments()
{
    watch('Comments');
    document.forms.DataForm.submit();
}

function watchAll()
{
    watch('Subreddits');
    watch('Users');
    watch('Posts');
    watch('Comments');
    document.forms.DataForm.submit();
}