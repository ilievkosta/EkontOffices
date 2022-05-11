<?php


class EcontRestC {
    public static function request($method, $param = array(), $timeout = 15) {

        $endpt = 'https://demo.econt.com/ee/services';

        // Please change this in Production
        $auth = array(
            'login' => 'iasp-dev',
            'password' => 'iasp-dev',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpt . '/' . rtrim($method,'/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        if(!empty($auth)) curl_setopt($ch, CURLOPT_USERPWD, $auth['login'].':'.$auth['password']);
        if(!empty($param)) curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($param));
        curl_setopt($ch, CURLOPT_TIMEOUT, !empty($timeout) && intval($timeout) ? $timeout : 4);
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonResponse = json_decode($response,true);
        if(!$jsonResponse) {
            throw new \Exception("Invalid response.");
        }
        if(strpos($httpStatus,'2') !== 0) {
            throw new \Exception(self::flatError($jsonResponse));//simple error handling by combining all the returned error's messages
        } else {
            return $jsonResponse;
        }
    }

    public static function flatError($err) {
        $msg = trim($err['message']);
        $innerMsgs = array();
        foreach ($err['innerErrors'] as $e) $innerMsgs[] = self::flatError($e);
        if (!empty($msg) && !empty($innerMsgs)) {
            $msg .= ": ";
        }
        return $msg . implode("; ", array_filter($innerMsgs));
    }
}


$offices=EcontRestC::request("Nomenclatures/NomenclaturesService.getOffices.json");

json_encode($offices);


$officesArr=array();
foreach($offices as $a => $a_value) {
    foreach ($a_value as $val) {

        array_push($officesArr,$val['name']);
    }

}

/*foreach ($officesArr as $city){
    echo $city;
    echo '<br>';
}*/
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head><!-- w   w w    .d  e    mo   2   s.    c  o  m-->
<body>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://unpkg.com/vuex@3.3.0/dist/vuex.js"></script>
<div id="app">

    <table>
        <thead>
        <tr>
            <th> Ekont Offices</th>

        </tr>
        </thead>
        <tbody v-if="orderProducts.length > 0">
        <tr v-for="(item, index) in orderProducts" :key="index">
            <td>{{item}}</td>

        </tr>
        </tbody>

    </table>
</div>



<script type='text/javascript'>
    Vue.use(Vuex);
    const store = new Vuex.Store({
        state: {
            orderProducts: <?php echo json_encode($officesArr); ?>
        },
        mutations: {
            addProduct(state, payload) {
                state.orderProducts.push(payload);
            }
        }
    })
    new Vue({
        store,
        el: '#app',

        computed: {
            orderProducts() {
                return this.$store.state.orderProducts;
            }
        },

    });
</script>
</body>
</html>