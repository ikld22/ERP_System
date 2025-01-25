import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.metrics import accuracy_score
import pickle

# فرض أن لدينا بيانات المستخدمين والوظائف كـ DataFrame
data = {
    'skills': ['PHP, MySQL, HTML', 'PHP, JavaScript', 'Python, Django', 'JavaScript, React'],
    'experience': [3, 2, 4, 2],
    'education': ['Bachelor\'s Degree', 'Bachelor\'s Degree', 'Master\'s Degree', 'Bachelor\'s Degree'],
    'is_matched': [1, 0, 1, 0]  # هل تم التوظيف (1: نعم، 0: لا)
}

df = pd.DataFrame(data)

# تحويل المهارات إلى تمثيل عددي
vectorizer = CountVectorizer()
X = vectorizer.fit_transform(df['skills'])
X = pd.concat([pd.DataFrame(X.toarray()), df[['experience', 'education']]], axis=1)
y = df['is_matched']

# تقسيم البيانات إلى مجموعة تدريب واختبار
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=42)

# تدريب نموذج الانحدار اللوجستي
model = LogisticRegression()
model.fit(X_train, y_train)

# اختبار النموذج
y_pred = model.predict(X_test)
print(f'Accuracy: {accuracy_score(y_test, y_pred)}')

# حفظ النموذج
with open('model.pkl', 'wb') as f:
    pickle.dump(model, f)
