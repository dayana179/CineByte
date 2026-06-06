function toggleMenu() {
  const nav = document.getElementById("mainNav");
  nav.classList.toggle("show");
}

/* =====================
   AUTHENTICATION
===================== */

function getUsers() {
  return JSON.parse(localStorage.getItem("cinebyteUsers")) || [];
}

function getCurrentUser() {
  return JSON.parse(localStorage.getItem("cinebyteCurrentUser"));
}

function signup(event) {
  event.preventDefault();

  const username = document.getElementById("signupUsername").value.trim();
  const email = document.getElementById("signupEmail").value.trim();
  const password = document.getElementById("signupPassword").value;

  let users = getUsers();

  if (users.find(user => user.email === email)) {
    alert("This email is already registered.");
    return;
  }

  const newUser = {
    username,
    email,
    password
  };

  users.push(newUser);
  localStorage.setItem("cinebyteUsers", JSON.stringify(users));
  localStorage.setItem("cinebyteCurrentUser", JSON.stringify(newUser));

  alert("Account created successfully.");
  window.location.href = "profile.html";
}

function login(event) {
  event.preventDefault();

  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value;

  const users = getUsers();
  const user = users.find(user => user.email === email && user.password === password);

  if (!user) {
    alert("Invalid email or password.");
    return;
  }

  localStorage.setItem("cinebyteCurrentUser", JSON.stringify(user));

  alert("Login successful.");
  window.location.href = "profile.html";
}

function logout() {
  localStorage.removeItem("cinebyteCurrentUser");
  alert("You have logged out.");
  window.location.href = "index.html";
}

function updateAuthNav() {
  const authNav = document.getElementById("authNav");
  const user = getCurrentUser();

  if (!authNav) return;

  if (user) {
    authNav.textContent = "Logout";
    authNav.href = "#";
    authNav.onclick = logout;
  } else {
    authNav.textContent = "Login";
    authNav.href = "auth.html";
  }
}

function protectPage() {
  const protectedPages = ["journal.html", "profile.html"];
  const currentPage = window.location.pathname.split("/").pop();
  const user = getCurrentUser();

  if (protectedPages.includes(currentPage) && !user) {
    document.querySelector("main").innerHTML = `
      <section class="protected-message">
        <h1>Login Required</h1>
        <p>You need to login or create an account to access personalised content.</p>
        <br>
        <a href="auth.html" class="btn">Login / Sign Up</a>
      </section>
    `;
  }
}

/* =====================
   MOVIE DATA
===================== */

const movies = {
  "Interstellar": {
    title: "Interstellar",
    genre: "Sci-fi • Adventure",
    poster: "https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg",
    desc: "A science fiction film about space travel, time, love, and survival."
  },
  "Oppenheimer": {
    title: "Oppenheimer",
    genre: "Drama • History",
    poster: "https://image.tmdb.org/t/p/w500/ptpr0kGAckfQkJeJIt8st5dglvd.jpg",
    desc: "A historical drama about J. Robert Oppenheimer and the development of the atomic bomb."
  },
  "The Batman": {
    title: "The Batman",
    genre: "Action • Crime",
    poster: "https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg",
    desc: "A dark detective-style superhero film following Batman in Gotham City."
  },
  "Dune": {
    title: "Dune",
    genre: "Sci-fi • Adventure",
    poster: "https://image.tmdb.org/t/p/w500/d5NXSklXo0qyIYkgV94XAgMIckC.jpg",
    desc: "A sci-fi epic about politics, power, survival, and destiny on the desert planet Arrakis."
  }
};

function searchMovies() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const cards = document.querySelectorAll(".movie-card");

  cards.forEach(card => {
    const title = card.dataset.title.toLowerCase();
    card.style.display = title.includes(input) ? "block" : "none";
  });
}

function loadFilmDetails() {
  const params = new URLSearchParams(window.location.search);
  const title = params.get("title") || "Interstellar";
  const movie = movies[title] || movies["Interstellar"];

  if (document.getElementById("detailTitle")) {
    document.getElementById("detailTitle").textContent = movie.title;
    document.getElementById("detailGenre").textContent = movie.genre;
    document.getElementById("detailPoster").src = movie.poster;
    document.getElementById("detailDesc").textContent = movie.desc;
  }
}

/* =====================
   PERSONALISED STORAGE
===================== */

function userKey(type) {
  const user = getCurrentUser();
  return user ? `${type}_${user.email}` : null;
}

function addToWatchlist() {
  const user = getCurrentUser();

  if (!user) {
    alert("Please login first to add this to your watchlist.");
    window.location.href = "auth.html";
    return;
  }

  const title = document.getElementById("detailTitle").textContent;
  const key = userKey("watchlist");
  let watchlist = JSON.parse(localStorage.getItem(key)) || [];

  if (!watchlist.includes(title)) {
    watchlist.push(title);
    localStorage.setItem(key, JSON.stringify(watchlist));
    alert(title + " added to your watchlist.");
  } else {
    alert(title + " is already in your watchlist.");
  }
}

function createList() {
  const user = getCurrentUser();

  if (!user) {
    alert("Please login first.");
    window.location.href = "auth.html";
    return;
  }

  const listName = document.getElementById("listName").value.trim();

  if (!listName) {
    alert("Please enter a list name.");
    return;
  }

  const key = userKey("createdLists");
  let lists = JSON.parse(localStorage.getItem(key)) || [];

  lists.push(listName);
  localStorage.setItem(key, JSON.stringify(lists));

  document.getElementById("listName").value = "";
  displayLists();
}

function displayLists() {
  const listContainer = document.getElementById("createdLists");
  if (!listContainer) return;

  const key = userKey("createdLists");
  const lists = JSON.parse(localStorage.getItem(key)) || [];

  listContainer.innerHTML = lists.length
    ? lists.map(list => `<li>${list}</li>`).join("")
    : "<li>No lists created yet.</li>";
}

function saveJournal(event) {
  event.preventDefault();

  const user = getCurrentUser();

  if (!user) {
    alert("Please login first.");
    window.location.href = "auth.html";
    return;
  }

  const title = document.getElementById("journalTitle").value.trim();
  const rating = document.getElementById("journalRating").value;
  const review = document.getElementById("journalReview").value.trim();

  const key = userKey("journals");
  let journals = JSON.parse(localStorage.getItem(key)) || [];

  journals.push({ title, rating, review });

  localStorage.setItem(key, JSON.stringify(journals));

  event.target.reset();
  displayJournals();
}

function displayJournals() {
  const journalList = document.getElementById("journalList");
  if (!journalList) return;

  const key = userKey("journals");
  const journals = JSON.parse(localStorage.getItem(key)) || [];

  journalList.innerHTML = journals.length
    ? journals.map(entry => `
        <article class="review-box">
          <h3>${entry.title}</h3>
          <p>${entry.rating}</p>
          <p>${entry.review}</p>
        </article>
      `).join("")
    : "<p>No journal entries yet.</p>";
}

function loadProfileData() {
  const user = getCurrentUser();
  if (!user) return;

  const profileInfo = document.getElementById("profileInfo");
  const watchlistBox = document.getElementById("profileWatchlist");
  const listsBox = document.getElementById("profileLists");
  const journalsBox = document.getElementById("profileJournals");

  if (profileInfo) {
    profileInfo.innerHTML = `
      <h2>Profile</h2>
      <p><strong>Username:</strong> ${user.username}</p>
      <p><strong>Email:</strong> ${user.email}</p>
      <button class="logout-btn" onclick="logout()">Logout</button>
    `;
  }

  if (watchlistBox) {
    const watchlist = JSON.parse(localStorage.getItem(userKey("watchlist"))) || [];
    watchlistBox.innerHTML = watchlist.length
      ? watchlist.map(item => `<li>${item}</li>`).join("")
      : "<li>No watchlist items yet.</li>";
  }

  if (listsBox) {
    const lists = JSON.parse(localStorage.getItem(userKey("createdLists"))) || [];
    listsBox.innerHTML = lists.length
      ? lists.map(item => `<li>${item}</li>`).join("")
      : "<li>No created lists yet.</li>";
  }

  if (journalsBox) {
    const journals = JSON.parse(localStorage.getItem(userKey("journals"))) || [];
    journalsBox.innerHTML = journals.length
      ? journals.map(j => `
          <article class="review-box">
            <h3>${j.title}</h3>
            <p>${j.rating}</p>
            <p>${j.review}</p>
          </article>
        `).join("")
      : "<p>No journal entries yet.</p>";
  }
}

/* =====================
   INIT
===================== */

updateAuthNav();
protectPage();
loadFilmDetails();
displayLists();
displayJournals();
loadProfileData();