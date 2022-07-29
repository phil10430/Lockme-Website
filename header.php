<!DOCTYPE html>
<html lang="en">
<head>
    <!-- <link rel="stylesheet" href="myStyle.css">  -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0;">
    <title>LockMe</title>
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    
    <!-- DateTimePicker stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
   
    <!-- My stylesheet must come after bootstrap styles to ensure priority -->
    <link rel="stylesheet" href="mystyle.css">

    <!-- DateTimePicker scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

    <!-- autorefresh site when lock/con status has changed -->
    <script  src="autorefresh_script.js"></script>
</head>

<body>
    <script>
        // load datetime picker
        $(function() {
            $('#datetimepicker1').datetimepicker({
                minDate: new Date(),
                format: 'DD/MM/YYYY HH:mm',
            });
        });

        // prevent post on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
