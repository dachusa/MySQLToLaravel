<?php
/**
 * User: dachusa
 * Date: 2/15/2016
 * Time: 12:44 PM
 */
    $myLaravel = new MyLaravelMigrate();
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
        <script type="text/javascript">function toggle_visibility(tableData){ if (tableData.classList.contains('active')) tableData.classList.remove('active'); else tableData.classList.add('active');}</script>
    </head>
    <body>
        <div class="container">
            <div class="row jumbotron">
                <div class="col-md-7">
                    <h1>My Laravel Migrate</h1>
                </div>
                <div class="col-md-5">
                    <form action="" method="post">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group"><label for="host">Host:</label> <input type="text" class="form-control" id="host" name="host" placeholder="ex. localhost" value="<?php echo $myLaravel->GetHost();?>" /></div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group"><label for="database">Database:</label> <input type="text" class="form-control" id="database" name="database" placeholder="test" value="<?php echo $myLaravel->GetDatabase();?>" /></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group"><label for="username">Username:</label> <input type="text" class="form-control" id="username" name="username" placeholder="root" value="<?php echo $myLaravel->GetUsername();?>" /></div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group"><label for="password">Password:</label> <input type="text" class="form-control" id="password" name="password" placeholder="pass123" value="<?php echo $myLaravel->GetPassword();?>" /></div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success pull-right" title="Connect"><span class="glyphicon glyphicon-play" aria-hidden="true"></span></button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <?php echo $myLaravel->GetMigrations(); ?>
        </div>
        <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
    </body>
</html><?php

class MyLaravelMigrate{
    private $connection;
    private $process=false;

    public function __construct()
    {
        $this->connection = self::GetConnectionData();
    }

    private function GetConnectionData(){
        $connection_host = "";
        $connection_database = "";
        $connection_username = "";
        $connection_password = "";

        if(isset($_POST['host'])) $connection_host = $_POST['host'];
        if(isset($_POST['database'])) $connection_database = $_POST['database'];
        if(isset($_POST['username'])) $connection_username = $_POST['username'];
        if(isset($_POST['password'])) $connection_password = $_POST['password'];

        if($connection_host!="" && $connection_database!="" && $connection_username!="" && $connection_password!="") $this->process=true;

        if(!$this->process) {
            $removeFromFileValue = "/[^a-zA-Z0-9]+/";
            $filePath = '../.env';
            if (file_exists($filePath)) {
                $handle = @fopen($filePath, "r");
                if ($handle) {
                    while (($buffer = fgets($handle, 4096)) !== false) {
                        if (strpos(strtoupper($buffer), "DB_HOST=") > -1) {
                            $connection_host = preg_replace($removeFromFileValue, '', after("=", $buffer));
                        }
                        if (strpos(strtoupper($buffer), "DB_DATABASE=") > -1) {
                            $connection_database = preg_replace($removeFromFileValue, '', after("=", $buffer));
                        }
                        if (strpos(strtoupper($buffer), "DB_USERNAME=") > -1) {
                            $connection_username = preg_replace($removeFromFileValue, '', after("=", $buffer));
                        }
                        if (strpos(strtoupper($buffer), "DB_PASSWORD=") > -1) {
                            $connection_password = preg_replace($removeFromFileValue, '', after("=", $buffer));
                        }
                    }
                    if (!feof($handle)) {
                        echo "Error: unexpected fgets() fail\n";
                    }
                    fclose($handle);
                }
            }

            if($connection_host!="" && $connection_database!="" && $connection_username!="" && $connection_password!="") $this->process=true;
        }
        return ["host"=>$connection_host, "database"=>$connection_database, "username"=>$connection_username, "password"=>$connection_password];
    }

    public function GetHost(){
        return $this->connection["host"];
    }

    public function GetDatabase(){
        return $this->connection["database"];
    }

    public function GetUsername(){
        return $this->connection["username"];
    }

    public function GetPassword(){
        return $this->connection["password"];
    }

    public function GetMigrations(){
        if(!$this->process) return;

        $output="";
        $tableLinks=[];
        $tableData=[];

        $db = new DB();
        $db->EstablishConnections($this->GetHost(), $this->GetDatabase(), $this->GetUsername(), $this->GetPassword(), $this->GetUsername(), $this->GetPassword());
        if($db) $output.= "<p>Connected to <b>".$this->GetDatabase()."</b> on <b>".$this->GetHost()."</b>.</p>";
        $query = "show tables;";
        $tables = $db->Query($query);
        $output .= "<p>Found " . count($tables) . " tables.</p>";
        foreach ($tables as $table) {
            $tablename = $table[0];
            $query = "describe `" . $tablename . "`;";

            $eloquentData = "<?php" . PHP_EOL
                . PHP_EOL
                . "use Illuminate\Database\Schema\Blueprint;" . PHP_EOL
                . "use Illuminate\Database\Migrations\Migration;" . PHP_EOL
                . PHP_EOL
                . "class Create".ucwords($tablename)."Table extends Migration" . PHP_EOL
                . "{" . PHP_EOL
                . indent() . "/**" . PHP_EOL
                . indent() . " * Run the migrations." . PHP_EOL
                . indent() . " *" . PHP_EOL
                . indent() . " * @return void" . PHP_EOL
                . indent() . " */" . PHP_EOL
                . indent() . "public function up()" . PHP_EOL
                . indent() . "{" . PHP_EOL
                . indent(2) . "if (!Schema::hasTable('" . $tablename . "')) {" . PHP_EOL
                . indent(3) . "Schema::create('" . $tablename . '\', function (Blueprint $table) {' . PHP_EOL;

            $columns = $db->Query($query);
            foreach ($columns as $columndata) {
                $eloquentData .= indent(4) . self::AddColumnByDataType($columndata) . ';' . PHP_EOL;
            }
            $eloquentData .= indent(3) . "});" . PHP_EOL
                . indent(2) ."}else{" . PHP_EOL;
            foreach ($columns as $columndata) {
                $eloquentData .= indent(3) . 'if (!Schema::hasColumn(\'' . $tablename . '\', \'' . $columndata["Field"] . '\')) {' . PHP_EOL
                    . indent(3) . "//" . PHP_EOL
                    . indent(4) . 'Schema::table(\'' . $tablename . '\', function ($table) {' . PHP_EOL
                    . indent(5) . self::AddColumnByDataType($columndata) . ';' . PHP_EOL
                    . indent(4) . '});' . PHP_EOL
                    . indent(3) . '}' . PHP_EOL
                    . PHP_EOL;
            }
            $eloquentData .= indent(2) . "}" . PHP_EOL
                . indent() . "}" . PHP_EOL
                . PHP_EOL;

            $eloquentData .= indent() . "/**" . PHP_EOL
                . indent() . " * Reverse the migrations." . PHP_EOL
                . indent() . " *" . PHP_EOL
                . indent() . " * @return void" . PHP_EOL
                . indent() . " */" . PHP_EOL
                . indent() . "public function down()" . PHP_EOL
                . indent() . "{" . PHP_EOL
                . indent(2) . "Schema::drop('$tablename');" . PHP_EOL
                . indent() . "}" . PHP_EOL;

            $eloquentData .= indent() . "/**" . PHP_EOL
                . indent() . " *" . PHP_EOL;
            foreach ($columns as $columndata) {
                $eloquentData .= indent() . " * " . $columndata["Field"] . "	" . $columndata["Type"] . "	" . $columndata["Null"] . "	" . $columndata["Key"] . "	" . $columndata["Default"] . "	" . $columndata["Extra"] . "	" . PHP_EOL;
            }
            $eloquentData .= indent() . " *" . PHP_EOL
                . indent() . " */" . PHP_EOL
                . "}";

            $tableLinks[] = "<li role='presentation'><a href='#table-$tablename' aria-controls='table-$tablename' role='tab' data-toggle='tab'>$tablename</a></li>";
            $tableData[] = "<div role='tabpanel' class='tab-pane' id='table-$tablename'><div class='well'><div class=''form-group'><label>$tablename</label><textarea class='form-control input-lg' rows='15'>$eloquentData</textarea></div></div></div>";
        }

        unset($db);

        $output .= "<div><ul class=\"nav nav-pills\" role=\"tablist\">";
        foreach($tableLinks as $tableLink){
            $output .= $tableLink;
        }
        $output .= "</ul><div class=\"tab-content\" style='margin-top:50px'>";
        foreach($tableData as $data){
            $output .= $data;
        }
        $output .= "</div></div>";

        return $output;
    }

    private function AddColumnByDataType($coldata)
    {
        $name = $coldata["Field"];
        $typedata = $coldata["Type"];
        $null = $coldata["Null"];
        $key = $coldata["Key"];
        $default = $coldata["Default"];
        $extra = $coldata["Extra"];

        $type = before('(', $typedata);
        $data = between('(', ')', $typedata);
        $info = after(')', $typedata);

        $eloquentCall = '$table->';

        switch (strtoupper($type)) {
            //      $table->bigIncrements('id');	Incrementing ID (primary key) using a "UNSIGNED BIG INTEGER" equivalent.
            //      $table->bigInteger('votes');	BIGINT equivalent for the database.
            case 'BIGINT':
                if (strpos(strtoupper($key),"PRI") > -1) {
                    $eloquentCall .= 'bigIncrements(\'' . $name . '\')';
                } else {
                    $eloquentCall .= 'bigInteger(\'' . $name . '\')';
                }
                break;
            //      $table->binary('data');	BLOB equivalent for the database.
            case 'BINARY':
                $eloquentCall .= 'binary(\'' . $name . '\')';
                break;
            //      $table->boolean('confirmed');	BOOLEAN equivalent for the database.
            case 'BOOLEAN':
                $eloquentCall .= 'boolean(\'' . $name . '\')';
                break;
            //      $table->char('name', 4);	CHAR equivalent with a length.
            case 'CHAR':
                $eloquentCall .= 'char(\'' . $name . '\', ' . $data . ')';
                break;
            //      $table->date('created_at');	DATE equivalent for the database.
            case 'DATE':
                $eloquentCall .= 'date(\'' . $name . '\')';
                break;
            //      $table->dateTime('created_at');	DATETIME equivalent for the database.
            case 'DATETIME':
                $eloquentCall .= 'dateTime(\'' . $name . '\')';
                break;
            //      $table->decimal('amount', 5, 2);	DECIMAL equivalent with a precision and scale.
            case 'DECIMAL':
                $eloquentCall .= 'decimal(\'' . $name . '\', ' . $data . ')';
                break;
            //      $table->double('column', 15, 8);	DOUBLE equivalent with precision, 15 digits in total and 8 after the decimal point.
            case 'DOUBLE':
                $eloquentCall .= 'double(\'' . $name . '\', ' . $data . ')';
                break;
            //      $table->enum('choices', ['foo', 'bar']);	ENUM equivalent for the database.
            case 'ENUM':
                $eloquentCall .= 'enum(\'' . $name . '\', [' . $data . '])';
                break;
            //      $table->float('amount');	FLOAT equivalent for the database.
            case 'FLOAT':
                $eloquentCall .= 'float(\'' . $name . '\')';
                break;
            //      $table->increments('id');	Incrementing ID (primary key) using a "UNSIGNED INTEGER" equivalent.
            //      $table->integer('votes');	INTEGER equivalent for the database.
            case 'INT':
                if (strpos(strtoupper($key), "PRI") > -1) {
                    $eloquentCall .= 'increments(\'' . $name . '\')';
                } else {
                    $eloquentCall .= 'integer(\'' . $name . '\')';
                }
                break;
            //      $table->json('options');	JSON equivalent for the database.
            case 'JSON':
                $eloquentCall .= 'json(\'' . $name . '\')';
                break;
            //      $table->jsonb('options');	JSONB equivalent for the database.
            case 'JSONB':
                $eloquentCall .= 'jsonb(\'' . $name . '\')';
                break;
            //      $table->longText('description');	LONGTEXT equivalent for the database.
            case 'LONGTEXT':
                $eloquentCall .= 'longText(\'' . $name . '\')';
                break;
            //      $table->mediumInteger('numbers');	MEDIUMINT equivalent for the database.
            case 'MEDIUMINT':
                $eloquentCall .= 'mediumInteger(\'' . $name . '\')';
                break;
            //      $table->mediumText('description');	MEDIUMTEXT equivalent for the database.
            case 'MEDIUMTEXT':
                $eloquentCall .= 'mediumText(\'' . $name . '\')';
                break;
            //      $table->morphs('taggable');	Adds INTEGER taggable_id and STRING taggable_type.
            case 'MORPHS':
                $eloquentCall .= 'morphs(\'' . $name . '\')';
                break;
            //      $table->nullableTimestamps();	Same as timestamps(), except allows NULLs.
            case 'NULL_TIMESTAMPS':
                $eloquentCall .= 'nullableTimestamps()';
                break;
            //      $table->rememberToken();	Adds remember_token as VARCHAR(100) NULL.
            case 'REMEMBER':
                $eloquentCall .= 'rememberToken()';
                break;
            //      $table->smallInteger('votes');	SMALLINT equivalent for the database.
            case 'SMALLINT':
                $eloquentCall .= 'smallInteger(\'' . $name . '\')';
                break;
            //      $table->softDeletes();	Adds deleted_at column for soft deletes.
            case 'SOFTDELETES':
                $eloquentCall .= 'softDeletes()';
                break;
            //      $table->string('email');	VARCHAR equivalent column.
            //      $table->string('name', 100);	VARCHAR equivalent with a length.
            case 'VARCHAR':
                if ($data != "") {
                    $eloquentCall .= 'string(\'' . $name . '\', ' . $data . ')';
                } else {
                    $eloquentCall .= 'string(\'' . $name . '\')';
                }
                break;
            //      $table->text('description');	TEXT equivalent for the database.
            case 'TEXT':
                $eloquentCall .= 'text(\'' . $name . '\')';
                break;
            //      $table->time('sunrise');	TIME equivalent for the database.
            case 'TIME':
                $eloquentCall .= 'time(\'' . $name . '\')';
                break;
            //      $table->tinyInteger('numbers');	TINYINT equivalent for the database.
            case 'TINYINT':
                $eloquentCall .= 'tinyInteger(\'' . $name . '\')';
                break;
            //      $table->timestamp('added_on');	TIMESTAMP equivalent for the database.
            case 'TIMESTAMP':
                $eloquentCall .= 'timestamp(\'' . $name . '\')';
                break;
            //      $table->timestamps();	Adds created_at and updated_at columns.
            case 'TIMESTAMPS':
                $eloquentCall .= 'timestamps()';
                break;
            //      $table->uuid('id');
            case 'UUID':
                $eloquentCall .= 'uuid(\'' . $name . '\')';
                break;
            default:
                return false;
        }
        if(strpos(strtoupper($info), " UNSIGNED") > -1){
            $eloquentCall .= "->unsigned()";
        }

        if(strtoupper($null) == "YES"){
            $eloquentCall .= "->nullable()";
        }

        if($default != ""){
            $eloquentCall .= "->default('$default')";
        }

        return $eloquentCall;
    }
}

//php helpers
class SQLParameter{
    public $parameter;public $value;public $dataType;
    public function __construct($parameter, $value, $dataType = "string"){$this->parameter = $parameter;$this->value = $value;
        switch ($dataType) {
            case "string":$this->dataType = PDO::PARAM_STR;break;
            case "int":case "integer":$this->dataType = PDO::PARAM_INT;break;
            case "bool":$this->dataType = PDO::PARAM_BOOL;break;
            case "null":$this->dataType = PDO::PARAM_NULL;break;
            case "datetime":$this->dataType = PDO::PARAM_STR;$this->value = date('Y-m-d H:i:s', strtotime($value));break;
            default: $this->dataType = PDO::PARAM_STR;
        }
    }
}

class DBConnection{public $readOnly;public $readWrite;public function __construct($readOnly, $readWrite){$this->readOnly = $readOnly;$this->readWrite = $readWrite;}}

class DB{
    private static $dbConnection;
    const DB_ErrorMessage = "Your request was not able to be completed due to a system error has occured";
    function EstablishConnections($host, $dbname, $rUser, $rPwd, $rwUser, $rwPwd){
        //Establish Read Only Connection
        if (!isset($mysql) || $mysql == null) {$mysqlReader = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $rUser, $rPwd);}
        //Establish Read Write Connection
        if (!isset($mysql) || $mysql == null) {$mysqlAdmin = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $rwUser, $rwPwd);}
        self::$dbConnection = new DBConnection($mysqlReader, $mysqlAdmin);
    }

    function CloseConnections(){self::$dbConnection = null;}

    function Query($sqlCommand, $sqlParameters = null)
    {
        try {
            $readOnly = self::$dbConnection->readOnly;
            $readOnly->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($sqlParameters != null) {
                $sqlQuery = $readOnly->prepare($sqlCommand);
                foreach ($sqlParameters as $sqlParameter) {
                    $sqlQuery->bindParam($sqlParameter->parameter, $sqlParameter->value, $sqlParameter->dataType);
                }
                $sqlQuery->execute();
                return $sqlQuery->fetchAll();
            } else {
                $sqlResponse = $readOnly->query($sqlCommand);
                return $sqlResponse->fetchAll();
            }
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
    }

    function QueryCount($sqlCommand, $sqlParameters = null)
    {
        try {
            $readOnly = self::$dbConnection->readOnly;
            $readOnly->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($sqlParameters != null) {
                $sqlQuery = $readOnly->prepare($sqlCommand);
                foreach ($sqlParameters as $sqlParameter) {
                    $sqlQuery->bindParam($sqlParameter->parameter, $sqlParameter->value, $sqlParameter->dataType);
                }
                $sqlQuery->execute();
                return sizeof($sqlQuery->fetchAll());
            } else {
                $readOnly->query($sqlCommand);
                $foundRows = $readOnly->query("SELECT FOUND_ROWS()")->fetchColumn();
                return $foundRows;
            }
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
    }

    function Execute($sqlCommand, $sqlParameters)
    {
        try {
            $readWrite = self::$dbConnection->readWrite;
            $readWrite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlQuery = $readWrite->prepare($sqlCommand);
            foreach ($sqlParameters as $sqlParameter) {
                $sqlQuery->bindParam($sqlParameter->parameter, $sqlParameter->value, $sqlParameter->dataType);
            }
            $sqlQuery->execute();
            return true;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
    }

    function ExecuteGetIdentity($sqlCommand, $sqlParameters)
    {
        try {
            $readWrite = self::$dbConnection->readWrite;
            $readWrite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlQuery = $readWrite->prepare($sqlCommand);
            foreach ($sqlParameters as $sqlParameter) {
                $sqlQuery->bindParam($sqlParameter->parameter, $sqlParameter->value, $sqlParameter->dataType);
            }
            $sqlQuery->execute();
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
        $selectIdentityCommand = "SELECT @@IDENTITY AS identity";
        $sqlQuery = $readWrite->prepare($selectIdentityCommand);
        $sqlQuery->execute();
        $identity = $sqlQuery->fetchAll();

        return $identity[0]["identity"];
    }
}

//String helpers
function indent($count=1){$indent = "    ";$indents = $indent;if ($count <= 1) {return $indents;} else {for ($i = 1; $i < $count; $i++) {$indents .= $indent;}return $indents;}}
function after($needle, $haystack){if (!is_bool(strpos($haystack, $needle)))return substr($haystack, strpos($haystack, $needle) + strlen($needle));}
function after_last($needle, $haystack){if (!is_bool(strrevpos($haystack, $needle)))return substr($haystack, strrevpos($haystack, $needle) + strlen($needle));}
function before($needle, $haystack){if(strpos($haystack, $needle)>-1){return substr($haystack, 0, strpos($haystack, $needle));}else{return $haystack;}}
function before_last($needle, $haystack){return substr($haystack, 0, strrevpos($haystack, $needle));}
function between($needleStart, $needleEnd, $haystack){return before($needleEnd, after($needleStart, $haystack));}
function between_last($needleStart, $needleEnd, $haystack){return after_last($needleStart, before_last($needleEnd, $haystack));}
function strrevpos($instr, $needle){$rev_pos = strpos(strrev($instr), strrev($needle));if ($rev_pos === false) return false; else return strlen($instr) - $rev_pos - strlen($needle);}

//Base Object
class BaseObject{
    public function __construct($params = array()){if ($params) {foreach ($params as $property => $value) {$this->__set($property, $value);}}}
    public function Get($property){return $this->__get($property);}
    public function Set($property, $value){return $this->__set($property, $value);}
    public function ObjectToArray(){return get_object_vars($this);}
    public function __get($property){$methodName = 'Get' . ucwords($property);if (method_exists($this, $methodName)) {return $this->$methodName();} else if (property_exists($this, $property)) {return $this->$property;}}
    public function __set($property, $value){$methodName = 'Set' . ucwords($property);if (method_exists($this, $methodName))return $this->$methodName($value); else if (property_exists($this, $property)) {$this->$property = $value;return true;} else {return false;}}
}