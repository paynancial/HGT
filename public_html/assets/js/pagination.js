// define the page names
const pageNames = ['pages-1', 'pages-2', 'pages-3', 'pages-4', 'pages-5'];

// define the current page
let currentPage = 1;

// define the page links
const pageLinks = [];

// generate the page links
pageNames.forEach((pageName, index) => {
  const pageLink = {
    text: index + 1,
    href: `${pageName}.php`
  };
  pageLinks.push(pageLink);
});

// render the pagination links
const paginationContainer = document.getElementById('pagination-container');
const paginationList = document.getElementById('pagination');

pageLinks.forEach((pageLink, index) => {
  const listItem = document.createElement('li');
  const link = document.createElement('a');
  link.textContent = pageLink.text;
  link.href = pageLink.href;
  listItem.appendChild(link);
  paginationList.appendChild(listItem);

  // add active class to the current page link
  if (index + 1 === currentPage) {
    listItem.classList.add('active');
  }
});

// add event listeners to the pagination links
paginationList.addEventListener('click', (event) => {
  if (event.target.tagName === 'A') {
    event.preventDefault();
    const nextPage = event.target.textContent;
    navigateToPage(nextPage);
  }
});

// navigate to the next or previous page
function navigateToPage(pageNumber) {
  // update the current page
  currentPage = parseInt(pageNumber);

  // update the pagination links
  paginationList.innerHTML = '';
  pageLinks.forEach((pageLink, index) => {
    const listItem = document.createElement('li');
    const link = document.createElement('a');
    link.textContent = pageLink.text;
    link.href = pageLink.href;
    listItem.appendChild(link);
    paginationList.appendChild(listItem);

    // add active class to the current page link
    if (index + 1 === currentPage) {
      listItem.classList.add('active');
    }
  });

  // load the next page
  const nextPageUrl = `${pageNames[currentPage - 1]}.php`;
  window.location.href = nextPageUrl;
}

// add next and previous buttons
const nextButton = document.createElement('button');
nextButton.textContent = 'Next';
nextButton.addEventListener('click', () => {
  navigateToPage(currentPage + 1);
});

const prevButton = document.createElement('button');
prevButton.textContent = 'Previous';
prevButton.addEventListener('click', () => {
  navigateToPage(currentPage - 1);
});

paginationContainer.appendChild(nextButton);
paginationContainer.appendChild(prevButton);