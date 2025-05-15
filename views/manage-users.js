// Edit User Modal
const editModal = document.getElementById("editModal");
const deleteModal = document.getElementById("deleteModal");
const closeButtons = document.querySelectorAll(".close");

document.querySelectorAll(".edit-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const userID = button.getAttribute("data-id");
        const name = button.getAttribute("data-name");
        const email = button.getAttribute("data-email");
        const phone = button.getAttribute("data-phone");
        const address = button.getAttribute("data-address");
        document.getElementById("editUserID").value = userID;
        document.getElementById("editName").value = name;
        document.getElementById("editEmail").value = email;
        document.getElementById("editPhone").value = phone;
        document.getElementById("editAddress").value = address;
        editModal.classList.add("show");
    });
});

// Delete User Modal
document.querySelectorAll(".delete-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const userID = button.getAttribute("data-id");
        document.getElementById("confirmDelete").setAttribute("data-id", userID);
        deleteModal.classList.add("show");
    });
});

// Close Modals
closeButtons.forEach((button) => {
    button.addEventListener("click", () => {
        editModal.classList.remove("show");
        deleteModal.classList.remove("show");
    });
});

// Confirm Delete
document.getElementById("confirmDelete").addEventListener("click", () => {
    const userID = document.getElementById("confirmDelete").getAttribute("data-id");
    // Example: Send delete request via AJAX (not implemented here)
    deleteModal.classList.remove("show");
});

// Allow closing modal by clicking outside modal-content
window.addEventListener("click", function(event) {
    if (event.target === editModal) {
        editModal.classList.remove("show");
    }
    if (event.target === deleteModal) {
        deleteModal.classList.remove("show");
    }
});