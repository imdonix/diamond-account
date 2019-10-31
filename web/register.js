var registrationErrorCodes = ["E701", "E702", "E703", "E711", "E712", "E713"];
var registrationErrorOutputs = 
["Your username do not meet the requirements!",
"Your In-Game name do not meet the requirements!",
"Your email do not meet the requirements",
"This username is already taken. Please choose a new one.", 
"This in-game is already taken. Please choose a new one.", 
"This e-mail address is already taken. Please enter another one."];

var successfullRegistrationOutput = "Congratulations! You have created your account, now you can log in your account.";
var regoutput = "alert";
var loginKeyLength = 32;


document.getElementById('submitbutton').addEventListener("click", function(e) 
{
    var username = document.forms["registrationform"]["un"].value;
    var ingamename = document.forms["registrationform"]["ign"].value;
    var email = document.forms["registrationform"]["email"].value;
    var password = document.forms["registrationform"]["ps"].value;
    var ref = document.forms["registrationform"]["ref"].value;
    SendRegInfo(username, ingamename, email, password, ref);
    return false;
});


function SendRegInfo()
{
  var request = new XMLHttpRequest();
  request.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200)
    {
      if (this.responseText.length == loginKeyLength)
      {
        document.getElementById("form").hidden = true;
        document.getElementById(regoutput).innerHTML = "<h2>Your accout has been created!</h2>";
        document.getElementById(regoutput).style.color = "green";
      }
      else
        for(i = 0; i < registrationErrorCodes.length; i++)
          if (this.responseText == registrationErrorCodes[i])
          {
            document.getElementById(regoutput).innerHTML = "<p>" + registrationErrorOutputs[i] + "</p>";
            document.getElementById(regoutput).style.color = "red";
          }
      return false;
    }

    return false;
  }

  var refurl = (arguments[4] != "") ? "&ref=" + arguments[4] : "";

  request.open("GET", "api.php?type=register&un=" + arguments[0] + refurl + "&ig=" + arguments[1] + "&ps=" + arguments[3] + "&email=" + arguments[2] + "&apikey=534b44a19bf18d20b71ecc4eb77c572f");
  request.send();

  return false;
}