export const depositInformation = () => {
  let depositNoticeElement = document.getElementsByName('planzer_notifications_deposit_notice');

  if (0 < depositNoticeElement.length) {
    let depositNoticeInformation = document.querySelector('input[name="planzer_notifications_deposit_notice_information"]');

    if ("2" !== document.querySelector('input[name="planzer_notifications_deposit_notice"]:checked').value) {
      depositNoticeInformation.closest('tr').classList.add('hidden');
    }

    depositNoticeElement.forEach(depo => depo.addEventListener('change', () => {
      ("2" !== depo.value) ? depositNoticeInformation.closest('tr').classList.add('hidden') : depositNoticeInformation.closest('tr').classList.remove('hidden');
    }));
  }
}