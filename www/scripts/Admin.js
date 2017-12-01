
function obliterate_subs()
{
    let input = document.getElementById('obliterate_subreddits');
    let subsStr = input.value;
    let subs = subsStr.split(';');

    subs = subs.filter(function(str) {return str != 'all' && str != ''; });

    if (subs.length == 0)
    {
        input.value = '';
        return;
    }

    input.value = JSON.stringify(subs);

    document.forms.ObliterateSubsForm.submit();
}