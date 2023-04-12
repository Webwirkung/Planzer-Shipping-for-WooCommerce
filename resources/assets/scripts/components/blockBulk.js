export const blockBulk = () => {
  let bulkForm = document.getElementById('posts-filter');

  if (bulkForm) {
    bulkForm.addEventListener('submit', () => {
      document.getElementById('doaction').disabled = true;
    });
  }
}