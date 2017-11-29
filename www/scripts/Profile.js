
function ValidatePhone(no)
{
    if (no === '')
    {
        return true;
    }

    if (!/^\d+$/.test(no))
    {
        return false;
    }
    
    if (no.length != 10)
    {
        return false;
    }


    return true;
}

function ValidateEmail(addr)
{
    if (addr === '')
    {
        return true;
    }

    if (!/.*@.*[.].*/.test(addr))
    {
        return false;
    }


    return true;
}

function Apply()
{
    let errElem = document.getElementById('ErrorMsg');
    let errs = [ ];
    let phoneIn = document.getElementById('telephone');
    let phoneNo = phoneIn.value;
    let phoneTmp = phoneNo;
    while ((phoneTmp = phoneNo.replace(' ', '').replace('(', '').replace(')', '')) != phoneNo)
    {
        phoneNo = phoneTmp;
    }
    phoneIn.value = phoneNo;
    if (!ValidatePhone(phoneNo))
    {
        errs.push('Invalid Phone Number! Format: "1234567890" or "123 456 7890"');
    }

    let emailIn = document.getElementById('email');
    let emailAddr = emailIn.value;
    if (!ValidateEmail(emailAddr))
    {
        errs.push('Invalid email syntax!');
    }




    if (errs.length > 0)
    {
        errElem.innerText = errs.join('\n');
        return;
    }

    
    document.forms.InfoForm.submit();


    errElem.innerText = '';
    return;
}