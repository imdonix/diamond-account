<?php
   header("Access-Control-Allow-Origin: *");
   if(isSet($_GET['ref']))
   {
      include 'lib/database.php';
      $req=GetRecordFromDB("users", "id", $_GET["ref"]);
      if(count($req) > 0)
         $ref_name= $req['name'];
   }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Diamond Account</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="web/style.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
   <div class="container">
      <div class="col-md-6 mx-auto text-center">
         <div class="header-title">
            <h1 class="wv-heading--title">
               Diamond account
            </h1>
            <?php if (isSet($ref_name)) include 'web/invited.php';?>
         </div>
      </div>
      <div class="row">
         <div class="col-md-4 mx-auto">
            <div id="form" class="myform form ">
               <form id="registrationform">
                  <input type="hidden" id="ref" name="ref" value="<?php if (isSet($ref_name)) echo($_GET['ref']); ?>">
                  
                  <div class="form-group">
                     <input type="text" name="un" min="3" max="20" class="form-control my-input" placeholder="Username">
                  </div>

                  <div class="form-group">
                     <input type="text" name="ign" min="3" max="20" class="form-control my-input" placeholder="IG name">
                  </div>

                  <div class="form-group">
                     <input type="email" name="email"  class="form-control my-input" placeholder="Email">
                  </div>

                  <div class="form-group">
                     <input type="password" min="5" name="ps" class="form-control my-input" placeholder="Password">
                  </div>
               </form>
   
               <div class="text-center ">
                     <button id="submitbutton" class=" btn btn-block send-button tx-tfm">Sing up</button>
               </div>

            </div>

            <div class="text-center ">
                  <div id="alert"></div>
            </div>

         </div>
      </div>
   </div>
   <script src="web/register.js"></script>
</body>
