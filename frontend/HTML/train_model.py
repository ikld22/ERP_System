import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import accuracy_score
import joblib
from faker import Faker
import random

# توليد بيانات عشوائية باستخدام مكتبة Faker
fake = Faker()

# عدد السجلات العشوائية التي نريد توليدها
num_records = 100

# توليد البيانات العشوائية
data = pd.DataFrame({
    'experience': [f"{random.randint(1, 10)} years" for _ in range(num_records)],  # الخبرة (1-10 سنوات)
    'skills': [','.join([fake.word() for _ in range(random.randint(3, 7))]) for _ in range(num_records)],  # المهارات (3-7 مهارات)
    'education': [random.choice(['Bachelor', 'Master', 'PhD']) for _ in range(num_records)]  # التعليم (بكالريوس، ماجستير، دكتوراه)
})

# إضافة عمود 'label' بناءً على الخبرة (أقل من 3 سنوات = Junior، 3 سنوات أو أكثر = Senior)
data['label'] = data['experience'].apply(lambda x: 'Junior' if int(x.split()[0]) < 3 else 'Senior')

# استخراج عدد السنوات من الخبرة
data['experience_years'] = data['experience'].apply(lambda x: int(x.split()[0]))  # استخراج السنوات من النص

# ترميز المهارات وعدد المهارات
data['skills_encoded'] = data['skills'].apply(lambda x: len(x.split(',')))  # عدد المهارات

# ترميز التعليم
data['education_encoded'] = data['education'].apply(lambda x: 3 if "Bachelor" in x else 4 if "Master" in x else 5)  # ترميز التعليم

# تجهيز البيانات للتدريب
X = data[['experience_years', 'skills_encoded', 'education_encoded']]  # الميزات
y = data['label']  # الهدف (التسمية)

# تقسيم البيانات إلى تدريب واختبار (80% تدريب و 20% اختبار)
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# تدريب نموذج شجرة القرار
model = DecisionTreeClassifier()
model.fit(X_train, y_train)

# التنبؤ باستخدام النموذج المدرب
y_pred = model.predict(X_test)

# قياس دقة النموذج
accuracy = accuracy_score(y_test, y_pred)
print(f"Model Accuracy: {accuracy * 100:.2f}%")

# حفظ النموذج باستخدام joblib
joblib.dump(model, 'employee_model.pkl')
print("Model saved as 'employee_model.pkl'")

# الآن، إضافة عمود "توافق مع الوظيفة" إلى البيانات:
# تحميل النموذج المدرب
loaded_model = joblib.load('employee_model.pkl')

# تجهيز البيانات للتنبؤ
X = data[['experience_years', 'skills_encoded', 'education_encoded']]

# التنبؤ بما إذا كان المتقدم يتوافق مع الوظيفة
predictions = loaded_model.predict(X)

# إضافة عمود "matched_for_job" بناءً على التنبؤات
data['matched_for_job'] = predictions

# الآن يمكنك عرض البيانات المحدثة
print(data[['experience', 'skills', 'education', 'matched_for_job']])

# حفظ البيانات في ملف CSV
data.to_csv('synthetic_data.csv', index=False)

print("Data updated and saved to 'synthetic_data.csv' successfully.")