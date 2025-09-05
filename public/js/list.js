var readRecordModal = document.getElementById('readRecordModal');
readRecordModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var petData = button.getAttribute('data-pet');
    if (petData) {
        var pet = JSON.parse(petData);
        document.getElementById('modal_pet_name').textContent = pet.pet_name || '';
        document.getElementById('modal_gender').textContent = pet.gender || '';
        document.getElementById('modal_species').textContent = pet.species || '';
        document.getElementById('modal_breed').textContent = pet.breed || '';
        document.getElementById('modal_owner_name').textContent = pet.owner_name || '';
        document.getElementById('modal_email').textContent = pet.email || '';
        document.getElementById('modal_owner_contact').textContent = pet.owner_contact || '';
        document.getElementById('modal_vaccination_status').textContent = pet.vaccination_status || '';
        document.getElementById('modal_medical_history').textContent = pet.medical_history || '';
        document.getElementById('modal_registration_date').textContent = pet.registration_date || '';

        // Handle profile picture display
        var profilePicture = document.getElementById('modal_profile_picture');
        var noPictureDiv = document.getElementById('modal_no_picture');

        if (pet.profile_picture && pet.profile_picture.trim() !== '') {
            profilePicture.src = 'uploads/pet_pics/' + pet.profile_picture;
            profilePicture.style.display = 'block';
            noPictureDiv.style.display = 'none';
        } else {
            profilePicture.style.display = 'none';
            noPictureDiv.style.display = 'block';
        }
    }
});