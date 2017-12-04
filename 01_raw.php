<?php
$layers = array(
  'mapservice/MapServer/1' => '地下水污染管制區位置圖',
  'mapservice/MapServer/2' => '土壤污染管制區位置圖',
  'mapservice/MapServer/3' => '土壤及地下水污染場址位置圖',
  'mapservice/MapServer/4' => '區域性地下水質測站位置圖',
  'mapservice/MapServer/5' => '土壤319公頃_一公頃一採樣點位置圖',
  'mapservice/MapServer/6' => '土壤319公頃_調查區一公頃範圍圖',
  'mapservice/MapServer/8' => '事業機構及處理再利用機構位置圖',
  'mapservice/MapServer/9' => '垃圾掩埋場位置圖',
  'mapservice/MapServer/10' => '垃圾掩埋場復育場址位置圖',
  'mapservice/MapServer/11' => '垃圾掩埋場範圍圖',
  'mapservice/MapServer/12' => '焚化爐廠區範圍圖',
  'mapservice/MapServer/14' => '交通噪音監測站位置圖',
  'mapservice/MapServer/15' => '環境噪音監測站位置圖',
  'mapservice/MapServer/17' => '機車定檢站位置圖',
  'mapservice/MapServer/18' => '空氣品質監測站位置圖',
  'mapservice/MapServer/19' => '紫外線測站位置圖',
  'mapservice/MapServer/20' => '特殊空品測站位置圖',
  'mapservice/MapServer/21' => '焚化爐煙囪位置圖',
  'mapservice/MapServer/22' => '空氣品質區範圍圖',
  'mapservice/MapServer/23' => '空氣污染防制區範圍圖',
  'mapservice/MapServer/25' => '海域水質監測站位置圖',
  'mapservice/MapServer/26' => '海灘水質監測站位置圖',
  'mapservice/MapServer/27' => '水庫水質監測站位置圖',
  'mapservice/MapServer/28' => '河川水質監測站位置圖',
  'mapservice/MapServer/29' => '河川巡守路線圖',
  'mapservice/MapServer/30' => '全國飲用水水源水質保護區範圍圖',
  'mapservice/MapServer/31' => '海洋棄置指定區域範圍圖',
  'mapservice/MapServer/32' => '水污染管制區範圍圖',
  'mapservice/MapServer/33' => '工業區污水處理廠分布位置圖',
  'mapservice/MapServer/35' => '環境檢測機構位置圖',
  'mapservice/MapServer/36' => '公害陳情案件分佈位置圖',
  'mapservice/MapServer/37' => '公害陳情警示區域範圍圖',
);
foreach($layers AS $layerUrl => $layerName) {
  $layerId = str_replace('/', '_', $layerUrl);
  $idFile = __DIR__ . '/raw/' . $layerId . 'Id';
  $layerPath = __DIR__ . '/raw/' . $layerId;
  if(!file_exists($layerPath)) {
    mkdir($layerPath, 0777, true);
  }
  if(!file_exists($idFile)) {
    file_put_contents($idFile, '0');
  }
  $lastId = intval(file_get_contents($idFile));
  $objects = array();

  while(++$lastId) {
    $objects[] = $lastId;
    if($lastId % 200 === 0) {
      $targetFile = $layerPath . '/data_' . $lastId . '.json';
      if(!file_exists($targetFile)) {
        $q = implode(',', $objects);
        $json = gzdecode(shell_exec("curl -k 'http://epagis.epa.gov.tw/EPAGIS/rest/services/{$layerUrl}/query?objectIds={$q}&outFields=*&returnGeometry=true&f=json' -H 'Host: epagis.epa.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0' -H 'Accept: */*' -H 'Accept-Language: en-US,en;q=0.5' -H 'Accept-Encoding: gzip, deflate, br' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Referer: http://epagis.epa.gov.tw/EPAGIS/rest/services/' -H 'Connection: keep-alive'"));
        $obj = json_decode($json, true);
        if(!isset($obj['features'][0])) {
          file_put_contents($idFile, $lastId);
          echo "{$layerId} done";
          break;
        }
        echo "processing {$layerId}/{{$lastId}}\n";
        file_put_contents($targetFile, $json);
      }
      $objects = array();
      file_put_contents($idFile, $lastId);
    }
  }
}
