#!/usr/bin/env python3

import json
from collections import defaultdict, Counter

rows = json.load(open('data/all_nak_pad_boy_girl.json'))
nak = json.load(open('data/nakshatras.json'))
mapnak = {n['id']: n for n in nak}


def most_common_map(get_key, idx):
    mp = defaultdict(Counter)
    for r in rows:
        key = get_key(r, mapnak)
        mp[key][r['ettu'][idx]] += 1
    out = {}
    for k, c in mp.items():
        out[k] = c.most_common(1)[0][0]
    return out

# Varna
varna_map = most_common_map(lambda r, m: (m[r['boy_nakshatra']]['varna'], m[r['girl_nakshatra']]['varna']), 0)
# Vashya
vashya_map = most_common_map(lambda r, m: (m[r['boy_nakshatra']]['vashya'], m[r['girl_nakshatra']]['vashya']), 1)
# Yoni
yoni_map = most_common_map(lambda r, m: (m[r['boy_nakshatra']]['yoni'], m[r['girl_nakshatra']]['yoni']), 4)
# Adhipathi
adhipathi_map = most_common_map(lambda r, m: (m[r['boy_nakshatra']]['lord'], m[r['girl_nakshatra']]['lord']), 5)

print('Varna entries:', len(varna_map))
print('Vashya entries:', len(vashya_map))
print('Yoni entries:', len(yoni_map))
print('Adhipathi entries:', len(adhipathi_map))

# Print a few entries for each
print('\nVarna sample:')
for i, (k, v) in enumerate(sorted(varna_map.items())):
    if i >= 10:
        break
    print(k, '->', v)

print('\nVashya sample:')
for i, (k, v) in enumerate(sorted(vashya_map.items())):
    if i >= 10:
        break
    print(k, '->', v)

print('\nYoni sample:')
for i, (k, v) in enumerate(sorted(yoni_map.items())):
    if i >= 10:
        break
    print(k, '->', v)

print('\nAdhipathi sample:')
for i, (k, v) in enumerate(sorted(adhipathi_map.items())):
    if i >= 10:
        break
    print(k, '->', v)
