<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/table_7">
</head>
<body>
<?php
header("Content-Type: text/html;charset=utf-8");
$servername = "192.168.1.227";
$username = "root";
$password = "123456";
$dbname = "attendance";

$start_date=$_POST["start_date"];
$end_date=$_POST["end_date"];

// 创建连接
$conn = mysqli_connect($servername, $username, $password, $dbname);
$conn->query("set names 'utf8';");
// 检测连接
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT
	a. NAME AS NAME,
	a.time AS starttime,
	b.time AS endtime
FROM
	attendance a
INNER JOIN attendance b ON a. NAME = b. NAME
WHERE
	(
		a.way = '面部'
		AND b.way = '指纹'
		AND TIMESTAMPDIFF(MINUTE, a.time, b.time) > 0
		AND TIMESTAMPDIFF(HOUR, a.time, b.time) < 8
		AND DATE_FORMAT(a.time, '%Y-%m-%d') BETWEEN '{$start_date}'
		AND '{$end_date}'
	)
GROUP BY
	a.time
ORDER BY
	a.`name`,
	b.time
	 ";
$result = $conn->query($sql);
$arr=$result->fetch_all();//获取sql数据信息

$time=array();//存储时间数组，仅包含每人每次有效的分钟数
$new_array=array();//[0]姓名，[1]时长（小时：分钟）,[2]时长（分钟）

for ($i=$k=0;$i<count($arr)-1;$i++)
{
    if ($arr[$i][0]==$arr[$i+1][0]){
            if ($arr[$i][2] != $arr[$i + 1][2]) {
                $new_array[$k][0] = $arr[$i][0];
                /*print_r($arr[$i][0]);
                echo "</td>";
                echo "<td>";
                print_r($arr[$i][1]);
                echo "</td>";
                echo "<td>";
                print_r($arr[$i][2]);
                echo "</td>";*/
                $new_array[$k][1] = $arr[$i][2] - $arr[$i][1];
                $new_array[$k][1] = minToTime(floor((strtotime($arr[$i][2]) - strtotime($arr[$i][1])) % 86400 / 60));

               /* echo "<td>";
                print_r($new_array[$k][1]);
                echo "</td>";*/
                $new_array[$k][2] = floor((strtotime($arr[$i][2]) - strtotime($arr[$i][1])) % 86400 / 60);
                $new_array[$k][3]=$arr[$i][1];
                $new_array[$k][4]=$arr[$i][2];
                $k++;
            } else {

            }

    }else{
        $new_array[$k][0]=$arr[$i][0];
       /* echo "<tr>";
        echo "<td>";
        print_r($arr[$i][0]);
        echo "</td>";
        echo "<td>";
        print_r($arr[$i][1]);
        echo "</td>";
        echo "<td>";
        print_r($arr[$i][2]);
        echo "</td>";*/
        $new_array[$k][1]=minToTime(floor((strtotime($arr[$i][2])-strtotime($arr[$i][1]))%86400/60));
       /* echo "<td>";
        print_r($new_array[$k][1]);
        echo "</td>";*/
        $new_array[$k][2]=floor((strtotime($arr[$i][2])-strtotime($arr[$i][1]))%86400/60);
        $new_array[$k][3]=$arr[$i][1];
        $new_array[$k][4]=$arr[$i][2];
        $k++;
    }
}
$new_array[$k][0]=$arr[$i][0];
/*echo "<tr>";
echo "<td>";
print_r($arr[$i][0]);
echo "</td>";
echo "<td>";
print_r($arr[$i][1]);
echo "</td>";
echo "<td>";
print_r($arr[$i][2]);
echo "</td>";*/
$new_array[$k][1]=minToTime(floor((strtotime($arr[$i][2])-strtotime($arr[$i][1]))%86400/60));
/*echo "<td>";
print_r($new_array[$k][1]);
echo "</td>";*/
$new_array[$k][2]=floor((strtotime($arr[$i][2])-strtotime($arr[$i][1]))%86400/60);
$new_array[$k][3]=$arr[$i][1];
$new_array[$k][4]=$arr[$i][2];
$k++;

$sum_time=array();
for ($n=0;$n<count($new_array);$n++)
{
    $time[$n]=$new_array[$n][2];
}
$sum_num=0;
for ($x=$m=0;$x<count($time)-1;$x++)
{
    if ($new_array[$x][0]==$new_array[$x+1][0]) {

    }else{
        /*print_r(array_slice($time,$m,$x-$m+1));*/
        $sum_time[$sum_num][0]=$new_array[$x][0];
        $sum_time[$sum_num][1]=minToTime(array_sum(array_slice($time,$m,$x-$m+1)));

        $m=$x+1;
        $sum_num++;
    }
}
/*print_r(array_slice($time,$m,count($time)-$m+1));*/

$sum_time[$sum_num][0]=$new_array[$x][0];
$sum_time[$sum_num][1]=minToTime(array_sum(array_slice($time,$m,$x-$m+1)));

$tt=array();
foreach ($sum_time as $k => $v){
    $tt[]=$v[1];
}
array_multisort($tt,SORT_NATURAL,SORT_DESC,$sum_time);
echo "<header>日期:";
echo $start_date;
echo "->";
echo $end_date;
echo "</header><br>";
echo
"<nav>
<table id='table-7',align='left'>
<thead>
    <th>排名</th>
    <th>姓名</th>
    <th>总时长(小时)</th>
    </thead>";
$time_num=1;
echo "<tbody>";
foreach ($sum_time as $k => $v){
    echo "<tr>";
    echo "<td>";
    print_r($time_num);
    echo "</td>";
    echo "<td>";
    print_r($v[0]);
    echo "</td>";
    echo "<td>";
    print_r($v[1]);
    echo "</td>";
    echo "</tr>";
    $time_num++;
}
echo "</tbody>";
echo "</table>
</nav>";

echo
"<section>
<table id='table-4' align='right'>
<thead>
    <th>姓名</th>
    <th>签到时间</th>
    <th>签退时间</th>
    <th>时长</th>
    </thead>";
echo "<tbody>";
foreach ($new_array as $k => $v){
    echo "<tr>";
    echo "<td>";
    print_r($v[0]);
    echo "</td>";
    echo "<td>";
    print_r($v[3]);
    echo "</td>";
    echo "<td>";
    print_r($v[4]);
    echo "</td>";
    echo "<td>";
    print_r($v[1]);
    echo "</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>
</section>";

echo "<footer>Copyright A315</footer>";
$conn->close();

function minToTime($times)
{
    $result = '00:00';
    if ($times > 0) {
        $hour = floor($times / 60);
        $minute = floor($times - 60 * $hour) ;
        if($minute<10){
            $result = $hour . ':0' . $minute ;
        }else{
            $result = $hour . ':' . $minute ;
        }
    }
    return $result;
}
    ?>

</table>
</body>
</html>
