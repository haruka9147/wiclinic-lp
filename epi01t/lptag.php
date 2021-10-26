<?php
	$p = $_GET["p"]; $cid = $_GET["cid"];
	if(!ctype_alnum($p) || !ctype_alnum($cid)) exit;
	setcookie("CL_".$p, $cid, time() + 63072000, "/", "wi-clinic.net");