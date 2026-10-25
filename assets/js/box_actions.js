let boxIdToRemove = null;
window.removeBox = function(boxId) {
  boxIdToRemove = String(boxId);
  document.getElementById('removeBoxName').textContent = boxIdToRemove;
  document.getElementById('removeBoxDialog').showModal();
};
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('confirmRemoveBtn')?.addEventListener('click', async () => {
    if (!boxIdToRemove) return;
    const boxId = boxIdToRemove;
    document.getElementById('removeBoxDialog').close();
    try {
      const res = await fetch('profile.php', {
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({removeBox:'1',boxId})
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Could not remove box.');
      const row = [...document.querySelectorAll('.box-item')].find(
        el => el.dataset.boxId === boxId
      );
      if (row) row.remove();
      else location.reload();
    } catch(e) { alert(e.message); }
  });
});
