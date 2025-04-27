<?php



class BD{

    private $conenntion;
    public function __construct( )
    {
     
    }


    public static function  conecction(){
        $json=file_get_contents('model/config.json');
        
        extract(json_decode($json, true)); 
         try {
             $newConnection= new PDO(
                 "$typeDB:host=$host;dbname=$dbName;charset=utf8mb4", 
                 $userDataBase, 
                 $password,
                 [
                     //parametros para la gestion de la conexion.
                     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                     PDO::ATTR_EMULATE_PREPARES => false
                 ]
             );
    
       
             return $newConnection;
            
         } catch (PDOException $e) {
             error_log("Error de conexión PDO: " . $e->getMessage());
             echo("Lo sentimos, ocurrió un error al conectar con la base de datos. Por favor intente más tarde.");
         return null;
         }
    }
}

class Table{

    protected $tableName;
    protected $conection;
    protected $campos=[];
    
    public function __construct(string $tableName, PDO $conection){
        $this->tableName=$tableName;
        $this->conection=$conection;
        $this->detectCamp();
    }

    private function detectCamp(){
        //funcion para devolver la informacion de una tabla
        $stmt= $this->conection->query("Describe {$this->tableName}");
        //el argumento fetch_colum, separa en columnas y trae una, sin indicarle una columna trae la numero 0 
        // esta contiene los nombres de los campos de la tabla, guardamos en una variable
        $this->campos=($stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    //La funcion recive un array asociativo -> [campo =>valor, campo=>valor]
    public function insertData(array $datos){
        //creamos un string utilizando los indices del array, es decir, todos los campos,
        $camposString=implode(", ", array_keys($datos));
        //creamos un string donde para cada valor de la insersion tenga ?
        $placeholder =implode(', ', array_fill(0, count($datos), '?'));  
        
        //creamos la sentencia  sql desde los anteriores string
        $sql= "INSERT INTO {$this->tableName} ({$camposString}) VALUES ({$placeholder})";

        
        try {
            //preparacion de la setencia
            $stmt=$this->conection->prepare($sql);

            //creamos un array con todos los valores del array asociativo
            $values=array_values($datos);

            //por medio de un bucle utilizamos bindValue para campo para evitar inyecciones sql
            //   la lista de valores de bindValue comienza desde 1, por tanto ahi que sumarle 1 al indice
            foreach($values as $indice => $value){
                $stmt->bindValue($indice + 1, $value);
             }

             $stmt->execute();
        } catch (ErrorException $e) {
            //caso que salga algun error, le falta contenido
            echo "Error en la consulta";
        }
      
    } 
    //recibe dos valores el campo y el valor para la comperacion del where en la sentencia
    public function readData($campo,$value){
        try {
            //ejecucion de la sentencia de busqueda
            $stm=$this->conection->query("SELECT * FROM $this->tableName WHERE {$campo} = {$value}");
            //retornamos los datos, por defecto, como un array asociativo
            return $stm->fetch();
        } catch (\Throwable $e) {
             //caso que salga algun error, le falta contenido
            echo "Error en la consulta";
            return [];
        }
      
    } 
    //recibe solo el id
    public function readDataId($value){

        // concional para verificar que el id entregado es valido, sea un string o entero
       if(is_numeric($value)){
        //convertimos el string en entero, por si acaso
        $value=(int)$value;
       }
       else{
           //caso que salga algun error, le falta contenido
        echo "erro con formato";
        return 0;
       }   
        //retornamos el resultado de  read data, solo que de una ves le decimos que el campo es id
        return    $this->readData("id", $value);
    }


    // entrega todos los elementos
    public function readAllData(){

        try {
            $stm=$this->conection->query("SELECT * FROM $this->tableName");
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
          //caso que salga algun error, le falta contenido
          echo "Error en la consulta";
          return [];
        }
      
      
    }

    //recibe los un array asociativo tal cual [campo=>value, campo value], recibe un campo y valor para el where
    public function updateData($datos, $campo, $value){
        //creamos un string para la sintanxis correcta de ? => para los valores que vamos a vincular
        $camposString=implode(" = ?, ", array_keys($datos));
        //añadimos al final por que solo con el primer codigo no quedarian todos campos cubiertos
        $camposString.=" = ? ";
        
        //Escribimos la sentencia
        $sql= "UPDATE  {$this->tableName} SET  $camposString  WHERE {$campo} = {$value}";
        
        try {
            //preparamos la setencia
            $stmt=$this->conection->prepare($sql);

             //creamos un array con todos los valores del array asociativo
            $values=array_values($datos);
              //vinculamos todos los valores con los ? por medio de un bucle.
            //   la lista de valores de bindValue comienza desde 1, por tanto ahi que sumarle 1 al indice
            foreach($values as $indice => $value){
                $stmt->bindValue($indice + 1, $value);
            }
          
            //ejecutamos
            $stmt->execute();
        } catch (\Throwable $e) {
             //caso que salga algun error, le falta contenido
             echo "Error en la consulta";
      
        }

      
    }

    //recibe el camop y valor para el where
    public function deleteData($campo, $value){
       //escribimos la consulta
        $sql="DELETE FROM {$this->tableName} WHERE {$campo} = :value";
        try {
            //preparamos la consulta
        $stmt=$this->conection->prepare($sql);
        //vinculamos el valor para evitar inyecciones sql
        $stmt->bindValue(":value", $value);
        $stmt->execute();

       } catch (\Throwable $e) {
         //caso que salga algun error, le falta contenido
         echo "Error en la consulta";
      
       }
        
      
      
    } 


}








?>