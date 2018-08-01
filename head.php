<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
<link rel="apple-touch-icon" type="image/x-icon" href="images/war_icon.png"/>
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/css.css">
<link rel="stylesheet" type="text/css" href="css/mobile.css">

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    function update() {
        let source = window.location.pathname.substr(1);
        source = source.slice(0, -4);
        let url;

        switch (source) {
            case 'war_decks':
                url = "query/update_war_decks.php";
                break;

            case 'player':
                url = "query/update_player.php?tag=".concat($('input:hidden[name=playerTagHidden]').val());
                break;

            case 'war':
                url = "query/update_war.php";
                break;

            case 'war_stats':
                url = "query/update_war_stats.php";
                break;

            case 'index':
                url = "query/update_clan.php";
                break;

            default:
                url = "";
                break;
        }

        if (url !== "") {
            $.ajax({
                url: url,
                beforeSend: function () {
                    $('#loaderDiv').show();
                    $('#navbar').collapse('hide');
                },
                success: function () {
                    window.location.reload(true);
                }
            });
        }
    }
</script>