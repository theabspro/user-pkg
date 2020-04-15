
<!DOCTYPE>
<html>
    <head>
        <title>User Invitation</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
                margin-bottom: 5px;
            }
            table:last-child {
                margin-bottom: 0;
            }
            td, th {
                border: 1px solid ;
                text-align: left;
                padding: 8px 4px 8px 4px;
                width: 50%;
                line-height: 1.2;
                font-size: 14px;

            }
            tr:nth-child(even) {
                background-color: #ffffff;
            }
        .button {
            display: block;
            width: 115px;
            height: 25px;
            background: #4E9CAF;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            color: white;
            font-weight: bold;
        }
    </style>
    </head>
    <body style="margin: 0 auto;
        width: 700px;
        min-height: 842px;">
        <!-- CUSTOMER ORDER FORM -->
        <table class="table table-bordered table-responsive" style="
            border: 2px solid #000;
            margin-top: 0px;
            position: relative;">
            <tbody>
                <tr>
                    <td style="padding: 4px;">
                        <table>
                            <tr >

                                <td style="text-align: center;  width: 100%;">
                                    <h2 style="font-size: 20px; margin-bottom: 0;">User Invitation</h2>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="2" style="border-right-color: transparent; border-bottom-color: transparent;text-align: center;">
                                   <h4><p>@UITOUX Solutions has invited you the PMS web portal<span></span></p></h4>

                                </td>
                            </tr>
                            <tr>
                            	<td style="text-align:center;">
                                    >User Name</a>
                                </td>
                                <td style="text-align:center;">
                                    >$user_name</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:center;">
                                    <?PHP $path = url('/login');?>
                                    <a href="{{$path}}" class="button">View Invitation</a>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </tbody>
        </table><!--CUSTOMER ORDER FORM-->

    </body>
</html>


