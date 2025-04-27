// Edit User Modal
const editModal = document.getElementById("editModal");
const deleteModal = document.getElementById("deleteModal");
const closeButtons = document.querySelectorAll(".close");

document.querySelectorAll(".edit-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const userID = button.getAttribute("data-id");
        // Fetch user data and populate the form
        document.getElementById("editUserID").value = userID;
        // Example: Fetch user details via AJAX (not implemented here)
        editModal.style.display = "block";
    });
});

// Delete User Modal
document.querySelectorAll(".delete-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const userID = button.getAttribute("data-id");
        document.getElementById("confirmDelete").setAttribute("data-id", userID);
        deleteModal.style.display = "block";
    });
});

// Close Modals
closeButtons.forEach((button) => {
    button.addEventListener("click", () => {
        editModal.style.display = "none";
        deleteModal.style.display = "none";
    });
});

// Confirm Delete
document.getElementById("confirmDelete").addEventListener("click", () => {
    const userID = document.getElementById("confirmDelete").getAttribute("data-id");
    // Example: Send delete request via AJAX (not implemented here)
    deleteModal.style.display = "none";
});