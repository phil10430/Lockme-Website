function calculateLockedDuration(entries) {
  const sorted = [...entries].sort((a,b) => new Date(a.created_at.replace(' ','T')) - new Date(b.created_at.replace(' ','T')));
  let totalMs=0, lockStart=null, lockingEntry=null;
  for (const entry of sorted) {
    const when = new Date(entry.created_at.replace(' ','T'));
    if (Number(entry.lock_status)===1 && !lockStart) { lockStart=when; lockingEntry=entry; }
    else if (Number(entry.lock_status)===0 && lockStart) {
      totalMs += Math.max(0,when-lockStart);
      lockStart=null; lockingEntry=null;
    }
  }
  let isCurrentlyLocked=lockStart!==null;
  if (lockStart) {
    let end=new Date();
    if (Number(lockingEntry.protection_level_timer)===1 && lockingEntry.open_time) {
      const expiry=new Date(lockingEntry.open_time.replace(' ','T'));
      if (!isNaN(expiry) && expiry<=end) {end=expiry;isCurrentlyLocked=false;}
    }
    totalMs+=Math.max(0,end-lockStart);
  }
  return {days:Math.floor(totalMs/86400000),hours:Math.floor(totalMs/3600000)%24,
    isCurrentlyLocked,lockingEntry:isCurrentlyLocked?lockingEntry:null};
}
function formatOpenTime(openTime) {
  if (!openTime) return '';
  const d=new Date(openTime.replace(' ','T'));
  return d.toLocaleDateString('de-DE')+' '+d.toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'});
}
function getCurrentStatus(entries) {
  if (!entries?.length) return {text:'No data',className:'box-status-none'};
  const duration=calculateLockedDuration(entries);
  if (!duration.isCurrentlyLocked) return {text:'Open',className:'box-status-open'};
  const e=duration.lockingEntry;
  let text='';
  if (e && Number(e.protection_level_timer)===1 && e.open_time) text=' until '+formatOpenTime(e.open_time);
  else if (e && Number(e.protection_level_password)===1) text=' by password ';
  return {text,className:'box-status-locked'};
}
function renderBoxStatuses() {
  Object.entries(window.boxHistoryData || {}).forEach(([boxId,entries]) => {
    const el=document.getElementById('box-status-'+boxId);
    if (!el) return;
    const status=getCurrentStatus(entries);
    el.textContent=status.text;
    el.className='box-status '+status.className;
  });
}
document.addEventListener('DOMContentLoaded',renderBoxStatuses);
