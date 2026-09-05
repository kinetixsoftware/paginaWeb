<html>

<head>
    <title>Problema</title>
</head>

<body>
    <form action="pagina2.php" method="post">
        Ingrese el mail del alumno:
        <input type="text" name="mail"><br>
        <input type="submit" value="buscar">
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

  $registros = mysqli_query($conexion, "SELECT * FROM alumnos WHERE mail='$_REQUEST[mail]'") or die("Problemas en el select:" . mysqli_error($conexion));
    if ($reg = mysqli_fetch_array($registros)) {
    ?>

    <form action="pagina3.php" method="post">
        Ingrese nuevo mail:
        <input type="text" name="mailnuevo" value="<?php echo $reg['mail'] ?>">
        <br>
        <input type="hidden" name="mailviejo" value="<?php echo $reg['mail'] ?>">
        <input type="submit" value="Modificar">
    </form>

    <?php
  } else
    echo "No existe alumno con dicho mail";
  ?>
</body>

</html>

<!-- ==================================================================== -->

<html>

<head>
    <title>Problema</title>
</head>

<body>
    <?php
    $conexion = mysqli_connect("localhost", "root", "", "base1") or die("Problemas con la conexión");

    mysqli_query($conexion,"UPDATE alumnos
                            SET mail='$_REQUEST[mailnuevo]' 
                            WHERE mail='$_REQUEST[mailviejo]'") or
        die("Problemas en el select:" . mysqli_error($conexion));
    echo "El mail fue modificado con exito";
    ?>
</body>

</html>