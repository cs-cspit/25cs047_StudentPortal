let events = [];
let currentPage = 1;
const recordsPerPage = 3;

/* GET HTML ELEMENTS */
const eventList = document.getElementById("eventList");
const searchEvent = document.getElementById("searchEvent");
const categoryFilter = document.getElementById("categoryFilter");
const sortEvent = document.getElementById("sortEvent");
const loadingMessage = document.getElementById("loadingMessage");

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const pageInfo = document.getElementById("pageInfo");

/* MODAL ELEMENTS */
const modal = document.getElementById("eventModal");
const modalTitle = document.getElementById("modalTitle");
const modalDate = document.getElementById("modalDate");
const modalVenue = document.getElementById("modalVenue");
const modalCategory = document.getElementById("modalCategory");
const modalDescription = document.getElementById("modalDescription");

const closeButton = document.querySelector(".close");
const closeButton2 = document.getElementById("closeBtn");

/* FETCH JSON DATA */
fetch("event.json")
    .then(response => {
        if (!response.ok) {
            throw new Error("Unable to load event.json");
        }
        return response.json();
    })
    .then(data => {
        events = data;
        loadingMessage.style.display = "none";
        displayEvents();
    })
    .catch(error => {
        console.log(error);
        loadingMessage.textContent = "Error loading events. Check event.json.";
    });

/* SEARCH, FILTER AND SORT */
function getEvents() {
    let result = [...events];
    const searchText = searchEvent.value.trim().toLowerCase();
    const category = categoryFilter.value;
    const sort = sortEvent.value;

    if (searchText !== "") {
        result = result.filter(event =>
            event.name.toLowerCase().includes(searchText) ||
            event.venue.toLowerCase().includes(searchText) ||
            event.category.toLowerCase().includes(searchText) ||
            event.description.toLowerCase().includes(searchText)
        );
    }

    if (category !== "All") {
        result = result.filter(event =>
            event.category === category
        );
    }

    if (sort === "dateAsc") {
        result.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );
    } else if (sort === "dateDesc") {
        result.sort((a, b) =>
            new Date(b.date) - new Date(a.date)
        );
    } else if (sort === "name") {
        result.sort((a, b) =>
            a.name.localeCompare(b.name)
        );
    }

    return result;
}

/* DISPLAY EVENTS */
function displayEvents() {
    const filteredEvents = getEvents();
    const totalPages = Math.ceil(
        filteredEvents.length / recordsPerPage
    );

    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    }

    const start = (currentPage - 1) * recordsPerPage;
    const end = start + recordsPerPage;
    const pageEvents = filteredEvents.slice(start, end);

    eventList.innerHTML = "";

    if (pageEvents.length === 0) {
        eventList.innerHTML = "<p>No events found.</p>";
        pageInfo.textContent = "Page 0";
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        return;
    }

    pageEvents.forEach(event => {
        const card = document.createElement("fieldset");
        card.className = "event-card";

        card.innerHTML = `
            <legend>
                <b>${event.name}</b>
            </legend>
            <p>Date : ${formatDate(event.date)}</p>
            <p>Venue : ${event.venue}</p>
            <button class="detailsBtn">View Details</button>
        `;

        const detailsButton = card.querySelector(".detailsBtn");

        detailsButton.onclick = function() {
            openModal(event);
        };

        eventList.appendChild(card);
    });

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
}

/* FORMAT DATE */
function formatDate(date) {
    const dateObject = new Date(date);

    return dateObject.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric"
    });
}

/* OPEN MODAL */
function openModal(event) {
    modalTitle.textContent = event.name;
    modalDate.textContent = formatDate(event.date);
    modalVenue.textContent = event.venue;
    modalCategory.textContent = event.category;
    modalDescription.textContent = event.description;
    modal.style.display = "block";
}

/* CLOSE MODAL */
function closeModal() {
    modal.style.display = "none";
}

/* CLOSE BUTTON */
closeButton.onclick = closeModal;
closeButton2.onclick = closeModal;

/* CLOSE WHEN CLICKING OUTSIDE */
window.onclick = function(event) {
    if (event.target === modal) {
        closeModal();
    }
};

/* SEARCH */
searchEvent.addEventListener("input", function() {
    currentPage = 1;
    displayEvents();
});

/* FILTER */
categoryFilter.addEventListener("change", function() {
    currentPage = 1;
    displayEvents();
});

/* SORT */
sortEvent.addEventListener("change", function() {
    currentPage = 1;
    displayEvents();
});

/* PREVIOUS PAGE */
prevBtn.addEventListener("click", function() {
    if (currentPage > 1) {
        currentPage--;
        displayEvents();
    }
});

/* NEXT PAGE */
nextBtn.addEventListener("click", function() {
    const filteredEvents = getEvents();
    const totalPages = Math.ceil(
        filteredEvents.length / recordsPerPage
    );

    if (currentPage < totalPages) {
        currentPage++;
        displayEvents();
    }
});