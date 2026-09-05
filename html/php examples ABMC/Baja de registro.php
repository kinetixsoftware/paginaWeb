<html>

<head>
<title>Problema</title>
</head>

<body>
<form action="pagina2.php" method="post">
Ingrese el mail del alumno a borrar:
<input type="text" name="mail">
<br>
<input type="submit" value="buscar y borrar">
</form>
</body>

</html>


<!-- ==================================================================== -->


<html>

<head>
<title>Problema</title>
</head>

<body>
    <?php
        $conexion = mysqli_connect("localhost", "root", "", "base1") or
        die("Problemas con la conexión");

        $registros = mysqli_query($conexion, "select codigo from alumnos where mail='$_REQUEST[mail]'") or die("Problemas en el select:" . mysqli_error($conexion));
        
        if ($reg = mysqli_fetch_array($registros)) {
            mysqli_query($conexion, "delete from alumnos where mail='$_REQUEST[mail]'") or die("Problemas en el select:" . mysqli_error($conexion));
            echo "Se efectuó el borrado del alumno con dicho mail.";
        } else {
            echo "No existe un alumno con ese mail.";
        }
        mysqli_close($conexion);
    ?>
</body>

</html>