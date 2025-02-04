window.quickChapterSelector = (translation = null) => {
    let books;
    const corpusSelectorButton = document.getElementById("corpusSelectorButton");
    const bookSelectorButton = document.getElementById('bookSelectorButton');    
    const bookSelector = document.getElementById('bookSelector');
    const bookSelectorList = document.getElementById("bookSelectorList");
    const chapterSelector = document.getElementById('chapterSelector');
    const chapterSelectorButton = document.getElementById('chapterSelectorButton');    
    const chapterSelectorList = document.getElementById("chapterSelectorList");

    const spinner = document.getElementById("selectorSpinner");            

    function showSpinner(show = true) { show ? spinner.classList.remove('hideSpinner') : spinner.classList.add('hideSpinner'); }

    const dropdownItems = document.querySelectorAll('#corpusSelector .dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', async (event) => {
                bookSelector.classList.add('hidden');
                chapterSelector.classList.add('hidden');
                bookSelectorButton.innerHTML = "Könyv <span class=\"caret\"></span>";
                chapterSelectorButton.innerHTML = "Fejezet <span class=\"caret\"></span>";
                showSpinner();
                const value = item.getAttribute("data-value");
                const itemHtmlValue = item.innerHTML;
                corpusSelectorButton.innerHTML = `<strong>${itemHtmlValue}</strong>`;

                const apiLink = translation ? `/api/books/${translation}` : '/api/books';
                const response = await fetch(apiLink);
                const bookResponse = await response.json();
                books = bookResponse.books;
                const filteredBooks = books.filter(book => book.corpus == value);    
                while (bookSelectorList.firstChild) {
                    bookSelectorList.removeChild(bookSelectorList.firstChild);
                }
                filteredBooks.forEach(book => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = '#';
                    a.setAttribute('data-booknumber', book.number);
                    a.setAttribute('data-abbrev', book.abbrev);
                    a.innerHTML = `<strong>${book.abbrev}</strong> <small>${book.name}</small>`;
                    li.appendChild(a);
                    a.addEventListener('click', async (event) => {
                        chapterSelector.classList.add('hidden');
                        showSpinner();                        
                        while (chapterSelectorList.firstChild) {
                            chapterSelectorList.removeChild(chapterSelectorList.firstChild);
                        }
                        const bookNumber = a.getAttribute("data-booknumber");
                        const selectedBook = books.find(book => book.number == bookNumber);    
                        bookSelectorButton.innerHTML = `<strong>${selectedBook.abbrev}</strong>`;
                        for (let i = 1; i <= selectedBook.chapterCount; i++) {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            let aLink = `/${selectedBook.abbrev}${i}`;                            
                            if (translation !== null) {
                                aLink = `/${translation}`+aLink;
                            }
                            a.href = aLink;
                            a.textContent = i;
                            li.appendChild(a);
                            chapterSelectorList.appendChild(li);
                          }
                        chapterSelector.classList.remove('hidden');

                        showSpinner(false);
                    });
                    bookSelectorList.appendChild(li);
                });
                showSpinner(false);
                bookSelector.classList.remove('hidden');

            });
        }
    );
};