<?php
require_once 'config.php';

// Aufgussplan laden aus DB
// Zeigt heutigen Tag + gestrigen Tag bis 02:00 Uhr
$aufguesse_heute = [];
$scents_map = [];
$current_hour = (int)date('H');
$show_date = date('Y-m-d'); // Standard: heute
// Vor 02:00 Uhr nachts auch gestrige Aufgüsse zeigen
if($current_hour < 2){
    $show_date = date('Y-m-d', strtotime('-1 day'));
}
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SELECT data_value FROM app_data WHERE data_key='v2'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row){
        $entries = json_decode($row['data_value'], true);
        if(is_array($entries)){
            foreach($entries as $e){
                if(isset($e['date']) && $e['date'] === $show_date){
                    $h = (int)$e['hour'];
                    $aufguesse_heute[$h] = $e;
                    $scents = array_filter([$e['d1']??'', $e['d2']??'', $e['d3']??'']);
                    if(!empty($scents)) $scents_map[$h] = array_values($scents);
                }
            }
        }
    }
} catch(Exception $e){}

// Standard Aufgussplan (Fallback wenn kein Eintrag)
$standard_plan = [
    11 => ['name'=>'Fit in den Tag', 'stil'=>'fruchtig'],
    12 => ['name'=>'Frühlingsduft', 'stil'=>'blumig'],
    13 => ['name'=>'Überraschung', 'stil'=>'frei wählbar'],
    14 => ['name'=>'Frische Brise', 'stil'=>'mit Menthol'],
    15 => ['name'=>'Pflegezeremonie', 'stil'=>'Düfte nach Wahl'],
    16 => ['name'=>'Blütenzauber', 'stil'=>'blumig'],
    17 => ['name'=>'Klangschale', 'stil'=>''],
    18 => ['name'=>'Frühlingswald', 'stil'=>'waldig'],
    19 => ['name'=>'Vitamin Bombe', 'stil'=>'fruchtig'],
    20 => ['name'=>'Überraschung', 'stil'=>'frei wählbar'],
    21 => ['name'=>'Heiß & Kalt', 'stil'=>'Menthol'],
    22 => ['name'=>'Ruhiger Abschluss', 'stil'=>'Düfte nach Wahl'],
];

// Wochentag bestimmen für letzten Aufguss
$dow = date('N'); // 1=Mo, 7=So
$last_slot = ($dow == 7) ? 19 : (($dow >= 1 && $dow <= 4) ? 21 : 22);

// Aufguss-Infos zusammenstellen
$slots_info = [];
foreach($standard_plan as $h => $plan){
    if($h > $last_slot) continue;
    $name = $plan['name'];
    $stil = $plan['stil'];
    $scents = [];
    if(isset($aufguesse_heute[$h])){
        $e = $aufguesse_heute[$h];
        if(!empty($e['note'])) $name = $e['note'];
        $scents = array_values(array_filter([$e['d1']??'', $e['d2']??'', $e['d3']??'']));
    }
    $slots_info[$h] = ['name'=>$name,'stil'=>$stil,'scents'=>$scents];
}

$slots_json = json_encode($slots_info, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>Aufguss bewerten — Bodetal Therme</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
body{font-family:'DM Sans','Segoe UI',system-ui,sans-serif;background:#f7f8fa;color:#1a1d23;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:16px 14px 50px}
.w{width:100%;max-width:440px}

/* Header */
.hdr{text-align:center;padding:20px 0 14px}
.hdr-icon{font-size:40px;margin-bottom:6px}
.hdr h1{font-size:18px;font-weight:700;color:#1a1d23}
.hdr p{font-size:13px;color:#5a6072;margin-top:2px}

/* Info Box */
.info-box{background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.2);border-radius:12px;padding:13px 15px;margin-bottom:13px;display:none}
.info-box.show{display:block}
.ib-label{font-size:10px;color:#5a6072;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.ib-time{font-size:26px;font-weight:700;color:#2563eb}
.ib-name{font-size:15px;font-weight:600;color:#1a1d23;margin-top:2px}
.ib-stil{font-size:12px;color:#5a6072;margin-top:1px}
.chips{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px}
.chip{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:20px;padding:3px 10px;font-size:11px;color:#5a6072}

/* Karten */
.card{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:12px;padding:15px;margin-bottom:11px}
.ct{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9aa0b0;margin-bottom:12px}

/* Zeit */
.tgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
.tb{padding:9px 3px;background:#eef0f4;border:2px solid transparent;border-radius:8px;font-size:12px;font-weight:600;text-align:center;color:#5a6072;cursor:pointer;transition:all .15s;font-family:inherit}
.tb:active{transform:scale(.95)}
.tb.sel{background:rgba(37,99,235,.08);border-color:#2563eb;color:#2563eb}
.tb.has-entry{border-color:rgba(37,99,235,.3);color:#2563eb}

/* Sterne */
.srow{display:flex;justify-content:center;gap:5px;margin-bottom:6px}
.sb{font-size:44px;background:none;border:none;cursor:pointer;opacity:.2;transition:all .15s;padding:2px;line-height:1;font-family:inherit}
.sb.lit{opacity:1}
.shint{text-align:center;font-size:13px;color:#d97706;font-weight:600;height:20px}

/* Intensität */
.igrid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
.ib{padding:12px 5px;background:#eef0f4;border:2px solid transparent;border-radius:10px;cursor:pointer;font-family:inherit;transition:all .15s;text-align:center}
.ib:active{transform:scale(.95)}
.ib .ii{font-size:26px}
.ib .il{font-size:10px;font-weight:600;color:#5a6072;margin-top:4px}
.ib.schwach{border-color:#d97706;background:rgba(217,119,6,.08)}
.ib.schwach .il{color:#d97706}
.ib.perfekt{border-color:#16a34a;background:rgba(22,163,74,.08)}
.ib.perfekt .il{color:#16a34a}
.ib.stark{border-color:#dc2626;background:rgba(220,38,38,.08)}
.ib.stark .il{color:#dc2626}

/* Düfte */
.si{background:#f7f8fa;border-radius:9px;padding:11px;margin-bottom:7px}
.si:last-child{margin-bottom:0}
.sn{font-size:13px;font-weight:600;margin-bottom:9px;color:#1a1d23}
.sopts{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px}
.so{padding:10px 4px;background:#fff;border:2px solid rgba(0,0,0,.08);border-radius:8px;cursor:pointer;font-family:inherit;text-align:center;transition:all .15s}
.so:active{transform:scale(.95)}
.so .oi{font-size:20px}
.so .ol{font-size:10px;font-weight:600;color:#9aa0b0;margin-top:3px}
.so.gut{border-color:#16a34a;background:rgba(22,163,74,.08)}
.so.gut .ol{color:#16a34a}
.so.ok{border-color:#d97706;background:rgba(217,119,6,.08)}
.so.ok .ol{color:#d97706}
.so.schlecht{border-color:#dc2626;background:rgba(220,38,38,.08)}
.so.schlecht .ol{color:#dc2626}

/* Kommentar */
textarea{width:100%;padding:11px;background:#f7f8fa;border:1px solid rgba(0,0,0,.08);border-radius:9px;color:#1a1d23;font-size:14px;font-family:inherit;resize:none;outline:none;min-height:80px}
textarea:focus{border-color:#2563eb}
textarea::placeholder{color:#9aa0b0}

/* Submit */
.sub{width:100%;padding:16px;background:#eef0f4;border:none;border-radius:12px;color:#9aa0b0;font-size:16px;font-weight:700;cursor:not-allowed;font-family:inherit;transition:all .2s;margin-bottom:7px}
.sub.ready{background:#16a34a;color:#fff;cursor:pointer}
.sub.ready:active{transform:scale(.98)}
.anon{text-align:center;font-size:11px;color:#9aa0b0}

/* Danke */
#danke{display:none;text-align:center;padding:60px 20px;width:100%}
#danke.show{display:flex;flex-direction:column;align-items:center;gap:12px}
</style>
</head>
<body>
<div class="w">

<div class="hdr">
  <div class="hdr-icon">🌿</div>
  <h1>Bodetal Therme Thale</h1>
  <p>Wie war Ihr Aufguss?</p>
</div>

<div class="info-box" id="info-box">
  <div class="ib-label">Ausgewählter Aufguss</div>
  <div class="ib-time" id="ib-time">--:00 Uhr</div>
  <div class="ib-name" id="ib-name">—</div>
  <div class="ib-stil" id="ib-stil"></div>
  <div class="chips" id="ib-chips"></div>
</div>

<div id="main-form">

<div class="card">
  <div class="ct">⏰ Welchen Aufguss bewerten?</div>
  <div class="tgrid" id="tgrid"></div>
</div>

<div class="card" id="card-stars" style="display:none">
  <div class="ct">⭐ Gesamteindruck</div>
  <div class="srow">
    <button class="sb" onclick="setStar(1)">⭐</button>
    <button class="sb" onclick="setStar(2)">⭐</button>
    <button class="sb" onclick="setStar(3)">⭐</button>
    <button class="sb" onclick="setStar(4)">⭐</button>
    <button class="sb" onclick="setStar(5)">⭐</button>
  </div>
  <div class="shint" id="shint"></div>
</div>

<div class="card" id="card-int" style="display:none">
  <div class="ct">🌡 Intensität</div>
  <div class="igrid">
    <button class="ib" onclick="setInt('schwach',this)"><div class="ii">🧊</div><div class="il">Zu schwach</div></button>
    <button class="ib" onclick="setInt('perfekt',this)"><div class="ii">😍</div><div class="il">Perfekt</div></button>
    <button class="ib" onclick="setInt('stark',this)"><div class="ii">🥵</div><div class="il">Zu stark</div></button>
  </div>
</div>

<div class="card" id="card-scents" style="display:none">
  <div class="ct">🌸 Düfte bewerten</div>
  <div id="scents-list"></div>
</div>

<div class="card" id="card-comment" style="display:none">
  <div class="ct">💬 Kommentar (optional)</div>
  <textarea id="comment" placeholder="Was hat Ihnen besonders gut gefallen? Was können wir verbessern?" rows="3"></textarea>
</div>

<button class="sub" id="sub-btn" onclick="doSubmit()">✓ Bewertung absenden</button>
<div class="anon">Vollständig anonym · keine persönlichen Daten</div>

</div>

<div id="danke">
  <div style="font-size:72px">🙏</div>
  <div style="font-size:26px;font-weight:700;color:#16a34a">Vielen Dank!</div>
  <div style="font-size:15px;color:#5a6072;text-align:center">Ihr Feedback hilft uns,<br>unsere Aufgüsse zu verbessern.</div>
  <div style="font-size:13px;color:#9aa0b0;margin-top:8px">Wir freuen uns auf Ihren nächsten Besuch!</div>
</div>

</div><!-- /w -->
<script>
const HINTS=['','😕 Nicht zufrieden','😐 Es geht','🙂 Gut','😊 Sehr gut','🤩 Ausgezeichnet!'];
const SCENT_EMOJI={
  'lavendel':'💜','eukalyptus':'🌿','menthol':'❄️','fichtennadel':'🌲','zitrone':'🍋',
  'orange':'🍊','grapefruit':'🍈','bergamotte':'🌸','minze':'🌱','pfefferminz':'💚',
  'rose':'🌹','jasmin':'🌺','vanille':'🤎','zimt':'🍂','ingwer':'🫚',
  'sandelholz':'🪵','zedernholz':'🌳','tanne':'🎄','kiefer':'🌲','latschenkiefer':'⛰️',
  'rosmarin':'🌿','thymian':'🌱','salbei':'🍃','kamille':'🌼','ylang':'🌺',
  'weihrauch':'🕯️','patschuli':'🌰','lemongrass':'🌾','limette':'💚','mandarine':'🍊',
  'nadelwald':'🌲','waldluft':'🍃','meeresbrise':'🌊','honig':'🍯','kokos':'🥥',
  'himalaya':'🏔️','sport':'⚡','vital':'💪','ice':'🧊','cool':'❄️'
};
function getEmoji(n){
  const l=n.toLowerCase();
  for(const[k,e] of Object.entries(SCENT_EMOJI)){if(l.includes(k))return e;}
  return '🌸';
}

const SLOTS=<?= $slots_json ?>;
let state={time:null,stars:0,intensity:null,scents:{},comment:''};

function buildGrid(){
  const now=new Date();
  const nowH=now.getHours();
  const g=document.getElementById('tgrid');
  g.innerHTML=Object.keys(SLOTS).map(h=>{
    h=parseInt(h);
    const isPast=h<=nowH;
    const hasEntry=SLOTS[h]&&SLOTS[h].scents&&SLOTS[h].scents.length>0;
    const cls='tb'+(hasEntry?' has-entry':'');
    return `<button class="${cls}" onclick="selectTime(${h})" data-h="${h}">${String(h).padStart(2,'0')}:00</button>`;
  }).join('');

  // Auto-select last past aufguss
  const pastSlots=Object.keys(SLOTS).map(Number).filter(h=>h<=nowH);
  if(pastSlots.length) selectTime(Math.max(...pastSlots));
}

function selectTime(h){
  document.querySelectorAll('.tb').forEach(b=>b.classList.remove('sel'));
  const btn=document.querySelector(`.tb[data-h="${h}"]`);
  if(btn)btn.classList.add('sel');
  state.time=h;
  state.scents={};

  const info=SLOTS[h]||{};
  document.getElementById('ib-time').textContent=String(h).padStart(2,'0')+':00 Uhr';
  document.getElementById('ib-name').textContent=info.name||'Aufguss';
  document.getElementById('ib-stil').textContent=info.stil||'';
  const scents=info.scents||[];
  document.getElementById('ib-chips').innerHTML=scents.map(s=>`<span class="chip">${getEmoji(s)} ${s}</span>`).join('');
  document.getElementById('info-box').classList.add('show');

  // Show cards
  ['card-stars','card-int','card-comment'].forEach(id=>document.getElementById(id).style.display='block');

  // Düfte
  if(scents.length){
    document.getElementById('card-scents').style.display='block';
    document.getElementById('scents-list').innerHTML=scents.map(s=>`
      <div class="si">
        <div class="sn">${getEmoji(s)} ${s}</div>
        <div class="sopts">
          <button class="so" onclick="setScent('${s.replace(/'/g,"\\'")}','gut',this)">
            <div class="oi">👍</div><div class="ol">Gut</div>
          </button>
          <button class="so" onclick="setScent('${s.replace(/'/g,"\\'")}','ok',this)">
            <div class="oi">😐</div><div class="ol">Ok</div>
          </button>
          <button class="so" onclick="setScent('${s.replace(/'/g,"\\'")}','schlecht',this)">
            <div class="oi">👎</div><div class="ol">Nicht so gut</div>
          </button>
        </div>
      </div>`).join('');
  } else {
    document.getElementById('card-scents').style.display='none';
  }

  // Reset
  state.stars=0;state.intensity=null;
  document.querySelectorAll('.sb').forEach(b=>b.classList.remove('lit'));
  document.querySelectorAll('.ib').forEach(b=>b.className='ib');
  document.getElementById('shint').textContent='';
  checkReady();
}

function setStar(n){
  state.stars=n;
  document.querySelectorAll('.sb').forEach((b,i)=>b.classList.toggle('lit',i<n));
  document.getElementById('shint').textContent=HINTS[n]||'';
  checkReady();
}

function setInt(v,el){
  state.intensity=v;
  document.querySelectorAll('.ib').forEach(b=>b.className='ib');
  el.classList.add(v);
  checkReady();
}

function setScent(name,val,el){
  state.scents[name]=val;
  el.closest('.sopts').querySelectorAll('.so').forEach(b=>b.className='so');
  el.classList.add(val);
}

function checkReady(){
  const ok=state.stars>0&&state.intensity!==null&&state.time!==null;
  document.getElementById('sub-btn').classList.toggle('ready',ok);
}

async function doSubmit(){
  if(!state.stars||!state.intensity||!state.time)return;
  const btn=document.getElementById('sub-btn');
  btn.textContent='Wird gespeichert...';btn.classList.remove('ready');
  const info=SLOTS[state.time]||{};
  const payload={
    stars:state.stars,intensity:state.intensity,scents:state.scents,
    comment:document.getElementById('comment').value.trim(),
    time:String(state.time).padStart(2,'0')+':00',
    aufguss_name:info.name||'',
    date:new Date().toISOString().split('T')[0],
    timestamp:Date.now(),source:'qr',id:Date.now().toString(36)
  };
  try{
    await fetch('api.php?action=save_rating',{method:'POST',
      headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  }catch(e){}
  document.getElementById('main-form').style.display='none';
  document.querySelector('.hdr').style.display='none';
  document.getElementById('info-box').style.display='none';
  document.getElementById('danke').classList.add('show');
}

// Show which date is being shown
const showDate = new Date();
if(showDate.getHours() < 2) showDate.setDate(showDate.getDate() - 1);
const dateStr = showDate.toLocaleDateString('de-DE', {weekday:'long', day:'2-digit', month:'2-digit', year:'numeric'});
const dateInfo = document.createElement('div');
dateInfo.style.cssText = 'text-align:center;font-size:12px;color:#9aa0b0;margin-bottom:14px;';
dateInfo.textContent = 'Aufgüsse vom ' + dateStr;
// Datum vor die erste .card-Box einsetzen (innerhalb main-form)
const mainForm=document.getElementById('main-form');
if(mainForm) mainForm.insertBefore(dateInfo, mainForm.firstChild);
buildGrid();
</script>
</body>
</html>
