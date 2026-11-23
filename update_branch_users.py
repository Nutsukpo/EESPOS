from pathlib import Path

file_path = Path("app/Models/Branch.php")
content = file_path.read_text()

method = """
    public function users(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
"""

if "public function users():" in content:
    print("The users() relationship already exists. No changes made.")
else:
    anchor = """    public function inventories(): HasMany
"""

    if anchor not in content:
        raise SystemExit("Could not find insertion point. No changes made.")

    content = content.replace(anchor, method + "\n" + anchor, 1)
    file_path.write_text(content)

    print("Successfully added users() relationship to Branch.php.")
