<?php

//classe dashboard
class Dashboard {

    public $data_inicio;
    public $data_fim;
    public $numeroVendas;
    public $totalVendas;
    public $clientesAtivos;
    public $clientesInativos;


    public function __get($atributo) {
        return $this->$atributo;
    }

    public function __set($atributo, $valor) {
        $this->$atributo = $valor;
        return $this;
    }


}

//classe de conexão bd
class Conexao {
    private $host = 'localhost';
    private $dbname = 'dashboard';
    private $user = 'root';
    private $pass = '';

    public function conectar(){
        try{

            $conexao = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname",
                "$this->user",
                "$this->pass"
            );

            $conexao->exec('set charset utf8');

            return $conexao;

        } catch (PDOException $e) {
            echo '<p>' .$e->getMessage() . '</p>';
        }
    }
}

//classe (model)
class Bd {
    private $conexao;
    private $dashboard;

    public function __construct($conexao, $dashboard) {
        $this->conexao = $conexao->conectar();
        $this->dashboard = $dashboard;
    }

    public function getNumeroVendas() {
        $query = '
            select
                count(*) as numero_vendas
            from
                tb_vendas
            where
                data_venda between :data_inicio and :data_fim';
        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->numero_vendas;
    }

    public function getTotalVendas() {
        $query = '
            select
                SUM(total) as total_vendas
            from
                tb_vendas
            where
                data_venda between :data_inicio and :data_fim';
        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->total_vendas;
    }

    public function getTotalAtivos() {
        $query = '
            select
                SUM(cliente_ativo) as total_ativos
            from
                tb_clientes
            where
                cliente_ativo > 0';
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->total_ativos;
    }

    public function getTotalInativos() {
        $query = '
        SELECT COUNT(cliente_ativo) FROM `tb_clientes` 
        WHERE cliente_ativo = 0;';
        $stmt = $this->conexao->prepare($query);
        $stmt->execute();
        return $stmt->fetch()[0];
    }
}

//lógica do script
$dashboard = new Dashboard();
$conexao =  new Conexao();

$competencia = explode('-', $_GET['competencia']);
$ano = $competencia[0];
$mes = $competencia[1];

$ultimo_dia_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

$dashboard->__set('data_inicio', $ano.'-'.$mes.'-01');
$dashboard->__set('data_fim', $ano.'-'.$mes.'-'.$ultimo_dia_mes);

$bd =  new Bd($conexao, $dashboard);

$dashboard->__set('numeroVendas', $bd->getNumeroVendas());
$dashboard->__set('totalVendas', $bd->getTotalVendas());
$dashboard->__set('clientesAtivos', $bd->getTotalAtivos());
$dashboard->__set('clientesInativos', $bd->getTotalInativos());

echo json_encode($dashboard);

//echo '<br>';
// print_r('total de vendas: ' . $dashboard->__get('totalVendas'));
// echo '<br>';
// print_r('total de ativos: ' . $bd->getTotalAtivos());
// echo '<br>';
// print_r('total de inativos: ' . $bd->getTotalInAtivos());
