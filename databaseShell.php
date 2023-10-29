<?

class DatabaseShell
{
    private $link;
    
    public function __construct($host, $user, $password, $database)
    {
        $this->link = mysqli_connect($host, $user, $password, $database);
        mysqli_query($this->link, "SET NAMES 'utf8'");
    }
    
    public function save($table, $row)
    {
        //формируем строки с названиями столбцов и значениями, которые будем присваивать
        $names = implode(', ', array_keys($row));
        $valuesArr = [];
        foreach ($row as $value)
        {
            //важно - добавляем кавычки для значений-строк в текст запроса
            if (is_string($value)) {

                $valuesArr[] = "'" . $value . "'";

            } else {

                $valuesArr[] = $value;
            }
        }

        $values = implode(', ', $valuesArr);

        $query = "INSERT INTO " . $table . " (" . $names . ") VALUES (" . $values . ")";
        mysqli_query($this->link, $query) or die(mysqli_error($this->link));
        //echo 'В таблицу ' . $table . ' добавлена строка со значениями: ' . $values;
    }
    
    public function del($table, $id)
    {
        //удаляем запись с заданным id
        $query = "DELETE FROM " . $table . " WHERE id=" . $id;
        mysqli_query($this->link, $query) or die(mysqli_error($this->link));
        echo 'В таблице ' . $table . ' удалена строка с id=' . $id . '<br>';
    }
    
    public function delAll($table, $ids)
    {
        //удаляем записи с заданным массивом id
        foreach ($ids as $id)
        {
            $this->del($table, $id);
        }
    }
    
    public function get($table, $id)
    {
        //получаем одну запись по заданному id
        $query = "SELECT * FROM " . $table . " WHERE ID=" . $id;
        $result = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
        return mysqli_fetch_assoc($result);
    }
    
    public function getAll($table, $ids)
    {
        //получаем массив записей по заданному массиву id
        $rows = [];
        foreach ($ids as $id)
        {
            $rows[] = $this->get($table, $id);
        }
        return $rows;
    }
    
    public function selectAll($table, $condition)
    {
        //получаем массив записей по условию, заданному строкой
        $query = "SELECT * FROM " . $table . " WHERE " . $condition;
        $result = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
        $num = mysqli_num_rows($result);
        $rows = [];
        for ($i=0; $i < $num; $i++)
        {
            $rows[] = mysqli_fetch_assoc($result);
        }
        return $rows;
    }
}