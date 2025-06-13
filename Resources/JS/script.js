//#####################################################################################

// Sprawdzanie czy użytkownik jest zalogowany

const isLoggedIn = () => {
  return localStorage.getItem("userToken");
};

// Przykład komunikacji z api.php na backendzie

async function loginUser() {
  const login = "admin"; // Zastąp rzeczywistymi danymi logowania
  const password = "admin"; // Zastąp rzeczywistymi danymi logowania

  try {
    // Wysłanie żądania logowania do backendu jako JSON z danymi logowania
    const response = await fetch("/backend/api.php/api/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ login, password }),
    });
    // Sprawdzenie odpowiedzi z serwera
    if (response.ok) {
      const data = await response.json();
      if (data.success) {
        localStorage.setItem("userToken", data.token);
      } else {
        alert(data.message || "Nie udało się zalogować");
      }
    }
  } catch (error) {
    // Obsługa błędów. W przypadku problemów z wysłaniem zapytania lub odpowiedzią z serwera
    console.error("Błąd podczas logowania:", error);
    alert("Wystąpił błąd podczas logowania. Spróbuj ponownie później.");
  }
}

//#########################################################################################