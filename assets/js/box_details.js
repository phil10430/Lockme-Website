let currentDetailsBoxId = null;
window.openBoxDetailsDialog = function(boxId) {
  currentDetailsBoxId = String(boxId);
  const d = (window.boxDetails && window.boxDetails[currentDetailsBoxId]) || {};
  document.getElementById('detailsBoxName').textContent = currentDetailsBoxId;
  document.getElementById('detailsNameTop').value = d.keyholder || '';
  document.getElementById('detailsBoxContent').value = d.box_content || '';
  document.getElementById('detailsError').style.display = 'none';
  document.getElementById('boxDetailsDialog').showModal();
};
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('boxDetailsForm');
  if (!form) return;
  form.addEventListener('submit', async ev => {
    ev.preventDefault();
    const error = document.getElementById('detailsError');
    error.style.display = 'none';
    const data = new FormData(form);
    data.set('saveBoxDetails','1');
    data.set('boxId',currentDetailsBoxId);
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;
    try {
      const res = await fetch(location.href,{method:'POST',body:data,credentials:'same-origin'});
      const result = await res.json();
      if (!res.ok || !result.success) throw new Error(result.message || 'Save failed.');
      location.reload();
    } catch(e) {
      error.textContent = e.message;
      error.style.display = 'block';
    } finally { button.disabled = false; }
  });
});
