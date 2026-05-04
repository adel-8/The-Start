import re
from pathlib import Path
view = Path('resources/views/admin/settings/index.blade.php').read_text(encoding='utf-8')
controller = Path('app/Http/Controllers/Admin/SettingsController.php').read_text(encoding='utf-8')
names = set(re.findall(r'name="([^"]+)"', view))
en_ar = sorted([n for n in names if n.endswith('_en') or n.endswith('_ar')])
missing = [n for n in en_ar if n not in controller]
print('count', len(en_ar))
print('missing', missing)
