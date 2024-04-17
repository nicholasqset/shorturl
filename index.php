<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Short URL</title>
        <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    </head>
    <body>
        <?php
        require './db.php';

        $base_url = '';

        if (isset($_GET['url']) && $_GET['url'] != "") {
            $url = urldecode($_GET['url']);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $slug = GetShortUrl($url);
                $conn->close();
                ?>
                <div class="container">
                    <h1>Paste Your Url</h1>
                    <form>
                        <p><input class="form-control"  type="url" name="url" required /></p>
                        <p><input class="btn btn-secondary" type="submit" /></p>
                    </form><br/>
                    <?php
                    echo 'Here is the short <a href="';
                    echo $base_url;
                    echo"/";
                    echo $slug;
                    echo '" target="_blank">';
                    echo 'link</a>: ';
                    ?><input class="form-control" type="text" value="<?php
                    echo $base_url;
                    echo"/";
                    echo $slug;
                    ?>" id="my_url_input">

                    <div class="sp_clipboard">
                        <button onclick="cpFunc()" onmouseout="dispFunc()">
                            <span class="sp_clipboardtext" id="sp_clipboard" >Copy to clipboard</span>
                        </button>
                    </div>
                </div>
                <?php
            } else {
                die("$url is not a valid URL");
            }
        } else {
            ?>
            <div class="container">
                <h1>Paste Your Url Here</h1>
                <form>
                    <p><input  type="url" name="url" required /></p>
                    <p><input type="submit" /></p>
                </form>
            </div>
            <?php
        }

        function GetShortUrl($url) {
            global $conn;
            $query = "SELECT * FROM shorturl WHERE url = '" . $url . "' ";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['shorturl'];
            } else {
                $shorturl = generateUniqueID();
                $sql = "INSERT INTO shorturl (url, shorturl, visits)
            VALUES ('" . $url . "', '" . $shorturl . "', '0')";
                if ($conn->query($sql) === true) {
                    return $shorturl;
                } else {
                    die("Unknown Error");
                }
            }
        }

        function generateUniqueID() {
            global $conn;
            $token = substr(md5(uniqid(rand(), true)), 0, 3);
            $query = "SELECT * FROM shorturl WHERE shorturl = '" . $token . "' ";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                generateUniqueID();
            } else {
                return $token;
            }
        }

        if (isset($_GET['redirect']) && $_GET['redirect'] != "") {
            $slug = urldecode($_GET['redirect']);

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $url = GetRedirectUrl($slug);
            $conn->close();
            header("location:" . $url);
            exit;
        }

        function GetRedirectUrl($slug) {
            global $conn;
            $query = "SELECT * FROM shorturl WHERE shorturl = '" . addslashes($slug) . "' ";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $visits = $row['visits'] + 1;
                $sql = "update shorturl set visits='" . $visits . "' where id='" . $row['id'] . "' ";
                $conn->query($sql);
                return $row['url'];
            } else {
                die("Invalid Link!");
            }
        }
        ?>
        <script>
            function cpFunc() {
                var cpText = document.getElementById("my_url_input");
                cpText.select();
                document.execCommand("copy");

                var sp_clipboard = document.getElementById("sp_clipboard");
                sp_clipboard.innerHTML = "Copied: " + cpText.value;
            }

            function dispFunc() {
                var sp_clipboard = document.getElementById("sp_clipboard");
                sp_clipboard.innerHTML = "Copy to clipboard";
            }
        </script>
    </body>
</html>
