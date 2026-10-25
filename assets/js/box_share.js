window.openShareDialog = function(boxId) {
  const url = new URL('box_share.php', window.location.href);
  url.searchParams.set('box_id',boxId);
  const link = url.toString();
  document.getElementById('shareBoxName').textContent = boxId;
  document.getElementById('shareLinkInput').value = link;
  document.getElementById('shareCopiedMsg').style.display = 'none';
  document.getElementById('sharePublicWarning').style.display =
    window.profileVisibility === 'public' ? 'none' : 'block';
  document.getElementById('shareTwitterLink').href =
    'https://twitter.com/intent/tweet?url=' + encodeURIComponent(link) +
    '&text=' + encodeURIComponent('My LockMeBox status:');
  document.getElementById('shareBlueskyLink').href =
    'https://bsky.app/intent/compose?text=' + encodeURIComponent('My LockMeBox status: ' + link);
  document.getElementById('shareDialog').showModal();
};
window.copyShareLink = async function() {
  const input = document.getElementById('shareLinkInput');
  try {
    if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(input.value);
    else { input.select(); if (!document.execCommand('copy')) throw new Error('Copy failed'); }
    document.getElementById('shareCopiedMsg').style.display = 'block';
  } catch(e) { alert('Could not copy the link. Please copy it manually.'); input.select(); }
};
