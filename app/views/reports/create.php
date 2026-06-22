<div class="container py-5">
    <div class="form-shell">
        <span class="eyebrow"><i data-lucide="circle-plus"></i> New report</span>
        <h1 class="fw-black mt-3">Report an Animal</h1>
        <p class="text-muted">Share accurate details to help the community respond faster.</p>

        <form method="post" action="?route=report-store" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label>Report Type</label>
                <select name="report_type" class="form-select" required>
                    <option value="missing">Missing Pet</option>
                    <option value="found">Found/Stray Animal</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Animal Name</label>
                <input name="animal_name" class="form-control" placeholder="Unknown if stray">
            </div>

            <div class="col-md-4">
                <label>Species</label>
                <input name="species" class="form-control" placeholder="Dog, Cat..." required>
            </div>

            <div class="col-md-4">
                <label>Breed</label>
                <input name="breed" class="form-control" placeholder="Shih Tzu, Puspin...">
            </div>

            <div class="col-md-4">
                <label>Color/Markings</label>
                <input name="color" class="form-control" placeholder="White, brown spots..." required>
            </div>

            <div class="col-md-4">
                <label>Gender</label>
                <select name="gender" class="form-select">
                    <option>Unknown</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>Date Seen/Missing</label>
                <input type="date" name="last_seen_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Main Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>

            <div class="col-12">
                <label>Location</label>
                <input name="location" class="form-control" placeholder="Barangay, landmark, city" required>
            </div>

            <div class="col-md-8">
                <label>Other Contact Information</label>
                <input name="owner_contact" class="form-control" placeholder="Phone number, Facebook, Messenger, alternate email, etc.">
                <small class="field-hint">Optional pero helpful ito kapag may nakakita agad sa pet.</small>
            </div>

            <div class="col-md-4">
                <label>Reward Amount</label>
                <input type="number" min="0" step="0.01" name="reward_amount" class="form-control" placeholder="Optional">
                <small class="field-hint">Optional reward for missing pet cases.</small>
            </div>

            <div class="col-12">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Collar, behavior, health condition, reward note, etc."></textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-brand btn-lg rounded-pill px-5">
                    <i data-lucide="send"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>
