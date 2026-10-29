<?php
ini_set("display_errors", "1");

        try 
        {
            $BDconn = mysqli_connect ("10.67.120.8", "25058", "Ni110809!", "25058");
            if (!$BDconn) {
                die("Ocorreu um erro no sistema! Os desenvolvedores ja foram avisados. Aguarde e tente novamente mais tarde!");
            }
        } 
        catch (Exception $e)
        {
            die('Ocorreu um erro: ' . $e->getMessage());
        }
            //echo("Conectado com sucesso!"."<br>");
        ?>