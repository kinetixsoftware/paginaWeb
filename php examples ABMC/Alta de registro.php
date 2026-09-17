Alta de registro
 <input type="text" name="nombre"><br>
    Ingrese mail:
    <input type="text" name="mail"><br>
    Seleccione el curso:
    <select name="codigocurso">
      <option value="1">PHP</option>
      <option value="2">ASP</option>
      <option value="3">JSP</option>
    </select>
    <br>
    <input type="submit" value="Registrar">
  </form>
</body>

</html>

<!-- ==================================================================== -->

<html>
<html>

<head>
  <title>Problema</title>
</head>

<body>
    <?php
    $conexion = mysqli_connect("localhost", "root", "", "base1") or
    die("Problemas con la conexión");

    mysqli_query($conexion, "INSERT INTO alumnos(nombre,mail,codigocurso) VALUES ('$_REQUEST[nombre]','$_REQUEST[mail]',$_REQUEST[codigocurso])")
    or die("Problemas en el select" . mysqli_error($conexion));

  mysqli_close($conexion);

  echo "El alumno fue dado de alta.";
  ?>
</body>

</html>