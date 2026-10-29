<?php
function CriaAlgo($tamanho) {
		  //                   1         2         3
		  if ($tamanho==0) { $tamanho=8; }
		  //INDICE   0123456789012345678901234567890
		  $sLetras ='ABCDEFGHJKLMNPQRSTUVXYWZ';
		  $sNumeros='0123456789';
		  $lnt=$tamanho;
		  $novaSenha='';
		  for( $lni=0; $lni<$lnt; $lni++) {
		    if (($lni % 2)==0) {
		     	$sorte=random_int(0, strlen($sLetras)-1);
		    	$novaSenha.=substr($sLetras,$sorte,1);
		    } else {
		     	$sorte=random_int(0, strlen($sNumeros)-1);
		    	$novaSenha.=substr($sNumeros,$sorte,1);
		    }
		  }
		  return $novaSenha;
}

function FazSenha($username,$password) {
		// Create a 256 bit (64 characters) long random salt
		// Let's add 'something random' and the username
		// to the salt as well for added security
		$salt = bin2hex(random_bytes(32));
		// Prefix the password with the salt
		//echo("Sal: ".$salt."<br>");
		$hash = $salt . $password;
		$loops=300; // 80 - 300
		// Hash the salted password a bunch of times
		for ( $i = 0; $i < $loops; $i ++ ) {
		  $hash = hash('sha256', $hash);
		  //echo($hash."<br>");
		}
		// Prefix the hash with the salt so we can find it back later
		$hash = $salt . $hash;
		//echo("Final: ".$hash."<br>");
		return $hash;
}

function ChecaSenha($password,$dbpassword) {
		// The first 64 characters of the hash is the salt
		$salt = substr($dbpassword, 0, 64);
		//echo("Sal: ".$salt."<br>");
		$hash = $salt . $password;
		// Hash the password as we did before
		$loops=300;
		for ( $i = 0; $i < $loops; $i ++ ) {
		  $hash = hash('sha256', $hash);
		  //echo($hash."<br>");
		}
		$hash = $salt . $hash;
		//echo("Final: ".$hash."<br>");
		if ( hash_equals((string)$dbpassword, $hash) ) {
			return true;
		} else {
			return false;
		}
}

function ResetaSenha($BDconn, $CPFPessoa)
{
    // Gera a senha temporária
    $senhaTemporaria = CriaAlgo(8);

    // Gera o hash
    $senhaHash = FazSenha($CPFPessoa, $senhaTemporaria);

    // Atualiza a senha no banco
    $sql = "UPDATE pessoa2DS
            SET senhaPessoa = ?,
                tipoSenhaPessoa = 'R',
                validaSenhaPessoa = ?,
                errosLoginPessoa = 0
            WHERE CPFpessoa2DS = ?";

    $stmt = mysqli_prepare($BDconn, $sql);

    if (!$stmt) {
        return false;
    }

    $data = date('Y-m-d H:i:s', time() + 15 * 60);

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $senhaHash,
        $data,
        $CPFPessoa
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    mysqli_stmt_close($stmt);

    // A senha original fica disponível
    return $senhaTemporaria;
}

?>
