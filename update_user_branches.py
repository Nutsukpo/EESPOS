from pathlib import Path

file_path = Path("app/Models/User.php")
content = file_path.read_text()

method = """
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)
            ->withTimestamps();
    }
"""

if "public function branches():" in content:
    print("The branches() relationship already exists. No changes made.")
else:
    anchor = """    public function ownedBusinesses(): HasMany
"""

    if anchor not in content:
        raise SystemExit("Could not find insertion point. No changes made.")

    content = content.replace(
        anchor,
        method + "\n" + anchor,
        1
    )

    file_path.write_text(content)
    print("Successfully added branches() relationship to User.php.")
